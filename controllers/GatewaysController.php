<?php

/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2017 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

namespace app\controllers;

use Yii;
use app\models\Gateway;
use app\models\GatewaySearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\GatewayUpload;
use app\helpers\Calc;

/**
 * GatewaysController implements the CRUD actions for Gateway model.
 */
class GatewaysController extends Controller {

  /**
   * @inheritdoc
   */
  public function behaviors() {
    return [
      'access' => [
        'class' => AccessControl::className(),
        'rules' => [
          [
            'allow' => true,
            'roles' => ['@']
          ]
        ],
      ],
      'verbs' => [
        'class' => VerbFilter::className(),
        'actions' => [
          'delete' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * Lists all Gateway models.
   * @return mixed
   */
  public function actionIndex() {
    $searchModel = new GatewaySearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
  }

  public function addGateway(&$gatewaysInFile, $lrrId, $latitude = null, $longitude = null) {
    if (strlen($lrrId) != 8) {
      return;
    }
    $lrrId = strtoupper($lrrId);
    if ($latitude === '') {
      $latitude = null;
    }
    if ($longitude === '') {
      $longitude = null;
    }

    if (isset($gatewaysInFile[$lrrId])) {
      $gatewaysInFile[$lrrId]['occurences'] += 1;
      if ($gatewaysInFile[$lrrId]['latitude'] == null && $gatewaysInFile[$lrrId]['longitude'] == null && $latitude != null && $longitude != null) {
        $gatewaysInFile[$lrrId]['latitude'] = $latitude;
        $gatewaysInFile[$lrrId]['longitude'] = $longitude;
      }
    } else {
      $gatewaysInFile[$lrrId] = [
        'latitude' => $latitude,
        'longitude' => $longitude,
        'occurences' => 1
      ];
    }

    return;
  }

  public function actionUpload() {
    $model = new GatewayUpload();
    $gatewaysInFile = [];

    // if new gateway file has been uploaded, parse content
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
      switch ($model->type) {
        case 'excel':
          if (($handle = @fopen($model->file->tempName, "r")) === false) {
            $model->addError('file', 'File could not be opened');
          } else {
            while (($fileLine = fgetcsv($handle, 0, ';')) !== false) {
              if ($fileLine[12] == "IN_DIENST") {
                $this->addGateway($gatewaysInFile, $fileLine[6], str_replace(',', '.', $fileLine[16]), str_replace(',', '.', $fileLine[17]));
              }
            }
          }
          break;
        case 'export':
          $str = file_get_contents($model->file->tempName);
          $re = '/\[([0-9a-f]{8})\].*gps\:real ([0-9\.]+)\,([0-9\.]*)/';
          $matches = [];

          preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

          $gatewaysInFile = [];
          foreach ($matches as $i => $match) {
            $this->addGateway($gatewaysInFile, $match[1], str_replace(',', '.', $match[2]), str_replace(',', '.', $match[3]));
          }
          break;
        default:
          throw new \yii\web\HttpException(400, 'Not yet supported');
      }
      // if there are gateways in the uploaded file
      if (count($gatewaysInFile) > 0) {
        // determine the type of gateway using the occurence
        foreach ($gatewaysInFile as &$gw) {
          $gw['type'] = ($gw['occurences'] < 3) ? 'omni' : 'sectorized';
          unset($gw['occurences']);
        }
        // store the new gateways in session and go to new action
        Yii::$app->session->set('newGateways', $gatewaysInFile);
        return $this->redirect(['upload-check']);
      } else {
        Yii::$app->session->addFlash('error', 'Could not find any gateways in the uploaded file');
      }
    }

    return $this->render('upload', [
        'model' => $model,
        'uploadPending' => (Yii::$app->session->has('newGateways'))
    ]);
  }

  public function actionUploadCheck() {
    if (!Yii::$app->session->has('newGateways')) {
      return $this->redirect(['upload']);
    }
    $itIsDone = false;

    // put new and current gateways in one operations array
    $newGateways = Yii::$app->session->get('newGateways');
    $currentGateways = Gateway::find()->where('1=1')->orderBy(['(deleted_at IS NULL)' => SORT_DESC, 'lrr_id' => SORT_ASC])->all();
    $nrActiveGateways = 0;
    $operations = [];
    foreach ($currentGateways as $gateway) {
      if ($gateway->deleted_at === null) {
        $nrActiveGateways += 1;
      }

      if (isset($newGateways[$gateway->lrr_id])) {
        $operations[] = [
          'lrrId' => $gateway->lrr_id,
          'new' => $newGateways[$gateway->lrr_id],
          'current' => $gateway
        ];
        unset($newGateways[$gateway->lrr_id]);
      } else {
        $operations[] = [
          'lrrId' => $gateway->lrr_id,
          'new' => null,
          'current' => $gateway
        ];
      }
    }
    foreach ($newGateways as $lrrId => $newGateway) {
      $operations[] = [
        'lrrId' => $lrrId,
        'new' => $newGateway,
        'current' => null
      ];
    }

    // determine actions to perform on gateway records
    $actions = ['delete' => 0, 'create' => 0, 'update' => 0, 'revive' => 0, null => 0];
    foreach ($operations as &$operation) {
      if ($operation['new'] === null) { //if no new record ..
        if ($operation['current']->deleted_at === null) { //and no deleted current record
          $operation['action'] = 'delete'; //delete
        } else {
          $operation['action'] = null; //keep deleted
        } //after this point, there is always $operation['new']
      } elseif ($operation['current'] === null) { //if no current record
        $operation['action'] = 'create'; //create
      } elseif ($operation['current']->deleted_at !== null) { //if current record deleted
        $operation['action'] = 'revive'; //revive it
      } elseif (($operation['current']->latitude == null || $operation['current']->longitude == null) && $operation['new']['latitude'] !== null && $operation['new']['longitude'] !== null) { //if current record incorrect location
        $operation['action'] = 'update'; //update it
      } elseif ($operation['new']['latitude'] != null && $operation['new']['longitude'] != null) { //if new record also has a location
        $distance = Calc::coordinateDistance($operation['current']->latitude, $operation['current']->longitude, $operation['new']['latitude'], $operation['new']['longitude']);
        $operation['distance'] = round($distance);
        //if there is a current and new record, update if more than 15 meters coordinate distance
        $operation['action'] = ($distance > 15) ? 'update' : null;
      } else {
        $operation['action'] = null;
      }

      $actions[$operation['action']] += 1;
    }

    // if update was confirmed
    if (Yii::$app->request->isPost) {
      foreach ($operations as &$op) {
        switch ($op['action']) {
          case 'delete':
            $op['result'] = $op['current']->delete();
            break;
          case 'create':
            $newGateway = new Gateway();
            $newGateway->lrr_id = $op['lrrId'];
            $newGateway->latitude = $op['new']['latitude'];
            $newGateway->longitude = $op['new']['longitude'];
            $newGateway->type = $op['new']['type'];
            $op['result'] = $newGateway->save();
            break;
          case 'update':
          case 'revive':
            $op['current']->latitude = $op['new']['latitude'];
            $op['current']->longitude = $op['new']['longitude'];
            $op['current']->type = $op['new']['type'];
            $op['current']->deleted_at = null;
            $op['result'] = $op['current']->save();
            break;
        }

        if (isset($op['result'])) {
          $op['action'] .= ($op['result'] ? ' success' : ' failed');
        } else {
          $op['action'] .= 'skipped';
        }
      }
      Yii::$app->session->remove('newGateways');
      $itIsDone = true;
    }


    return $this->render('upload-check', [
        'operations' => $operations,
        'itIsDone' => $itIsDone,
        'actions' => $actions,
        'nrActiveGateways' => $nrActiveGateways
    ]);
  }

  /**
   * Displays a single Gateway model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id) {
    return $this->render('view', [
        'model' => $this->findModel($id),
    ]);
  }

  /**
   * Creates a new Gateway model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate() {
    $model = new Gateway();

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    } else {
      return $this->render('create', [
          'model' => $model,
      ]);
    }
  }

  /**
   * Updates an existing Gateway model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id) {
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    } else {
      return $this->render('update', [
          'model' => $model,
      ]);
    }
  }

  /**
   * Deletes an existing Gateway model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id) {
    $this->findModel($id)->delete();

    return $this->redirect(['index']);
  }

  /**
   * Finds the Gateway model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Gateway the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = Gateway::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
