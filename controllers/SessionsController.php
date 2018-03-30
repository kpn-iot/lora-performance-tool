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

use app\models\Device;
use app\models\Frame;
use Yii;
use app\models\Session;
use app\models\SessionSearch;
use app\models\SessionSet;
use app\models\SessionSplitForm;
use yii\base\ErrorException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\lora\SessionCollection;

/**
 * SessionsController implements the CRUD actions for Session model.
 */
class SessionsController extends Controller {

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

  public function actionIndex() {
    $searchModel = new SessionSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    try {
      $device_id = Yii::$app->request->queryParams['SessionSearch']['device_id'];
    } catch (ErrorException $e) {
      $device_id = null;
    }

    $devices = Device::find()->orderBy(['name' => SORT_ASC])->all();
    $devicesFilter = [];
    foreach ($devices as $device) {
      $devicesFilter[$device->id] = $device->name;
    }

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'model' => ($device_id != null) ? Device::findOne($device_id) : null,
        'devicesFilter' => $devicesFilter,
        'sessionSets' => array_map(function($item) {
            return $item->name;
          }, SessionSet::find()->orderBy(['name' => SORT_ASC])->indexBy('id')->all())
    ]);
  }

  private function sessionBaseFind() {
    return Session::find()->with(['device', 'frames', 'lastFrame', 'firstFrame', 'frames.session', 'frames.session.device', 'frames.reception', 'frames.reception.gateway', 'frames.reception.frame', 'frames.reception.frame.session']);
  }

  public function actionView($id) {
    return $this->redirect(['report-coverage', 'id' => $id]);
  }

  public function actionReportCoverage($id) {
    $multiSession = explode('.', $id);
    if (count($multiSession) == 1) {
      $session = $this->sessionBaseFind()->andWhere(['id' => $id])->one();
      if ($session === null) {
        throw new \yii\web\HttpException(404, 'Session not found');
      }

      return $this->render('report-coverage', ['model' => $session]);
    } else {
      $sessions = $this->sessionBaseFind()->andWhere(['id' => $multiSession])->all();
      $sessionCollection = new SessionCollection($sessions);

      return $this->render('multi/report-coverage', [
          'sessionCollection' => $sessionCollection
      ]);
    }
  }

  /**
   * Displays a single Session model.
   * @param string $id
   * @return mixed
   */
  public function actionReportGeoloc($id) {
    $multiSession = explode('.', $id);
    if (count($multiSession) == 1) {
      return $this->render('report-geoloc', ['model' => $this->sessionBaseFind()->andWhere(['id' => $id])->one()]);
    } else {
      $sessions = $this->sessionBaseFind()->andWhere(['id' => $multiSession])->all();
      $sessionCollection = new SessionCollection($sessions);

      return $this->render('multi/report-geoloc', [
          'sessionCollection' => $sessionCollection
      ]);
    }
  }

  /**
   * Updates an existing Session model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id) {
    $model = $this->findModel($id);
    $sessionSetRecords = SessionSet::find()->orderBy(['name' => SORT_ASC])->all();
    $sessionSets = [];
    $currentSessionSets = [];
    foreach ($model->sessionSets as $currentSessionSet) {
      $currentSessionSets[$currentSessionSet->id] = $currentSessionSet->name;
    }
    foreach ($sessionSetRecords as $record) {
      if (in_array($record->id, array_keys($currentSessionSets))) {
        continue;
      }
      $sessionSets[$record->id] = $record->name;
    }

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      $model->updateProperties();
      Yii::$app->session->addFlash("success", "Session updated!");
      return $this->refresh();
    } else {
      return $this->render('update', [
          'model' => $model,
          'sessionSets' => $sessionSets
      ]);
    }
  }

  public function actionSplit($id) {
    $currentSession = $this->findModel($id);
    $splitForm = new SessionSplitForm();

    if ($splitForm->load(Yii::$app->request->post()) && $splitForm->validate()) {
      $frameCheck = Frame::find()->andWhere('count_up >= :count_up AND session_id = :session_id', [
          'count_up' => $splitForm->frameCounter,
          'session_id' => $id
        ])->orderBy(['count_up' => SORT_ASC])->one();
      if ($frameCheck === null) {
        throw new \yii\web\HttpException(404, 'Frame not found');
      }
      $newSession = new Session();
      $newSession->device_id = $currentSession->device_id;
      $newSession->description = 'Split from session ' . $currentSession->id;
      if ($splitForm->copyProperties) {
        $newSession->type = $currentSession->type;
        $newSession->vehicle_type = $currentSession->vehicle_type;
        $newSession->motion_indicator = $currentSession->motion_indicator;
        $newSession->location_id = $currentSession->location_id;
        $newSession->latitude = $currentSession->latitude;
        $newSession->longitude = $currentSession->longitude;
      }

      if (!$newSession->save()) {
        throw new \yii\web\HttpException(500, 'Session not created: ' . json_encode($newSession->errors));
      }

      Frame::updateAll(['session_id' => $newSession->id], 'session_id = :session_id AND count_up >= :frame_counter', [
        'session_id' => $currentSession->id,
        'frame_counter' => $frameCheck->count_up
      ]);

      $currentSession->updateProperties();
      $newSession->updateProperties();

      return $this->redirect(['view', 'id' => $newSession->id]);
    }

    return $this->render('split', [
        'model' => $currentSession,
        'splitForm' => $splitForm
    ]);
  }

  /**
   * Deletes an existing Session model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param string $id
   * @return mixed
   */
  public function actionDelete($id) {
    $model = $this->findModel($id);
    $deviceId = $model->device_id;
    $model->delete();

    return $this->redirect(['index', 'SessionSearch[device_id]' => $deviceId]);
  }

  /**
   * Finds the Session model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Session the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = Session::find()->with('device')->andWhere(['id' => $id])->one()) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
