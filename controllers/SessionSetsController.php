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

use app\components\WordExport;
use app\models\Frame;
use app\models\Session;
use app\models\SessionSet;
use app\models\SessionSetLink;
use app\models\SessionSetSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * SessionSetsController implements the CRUD actions for SessionSet model.
 */
class SessionSetsController extends Controller {

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
            ]
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
   * Lists all SessionSet models.
   * @return mixed
   */
  public function actionIndex() {
    $searchModel = new SessionSetSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Displays a single SessionSet model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id) {
    return $this->render('view', [
        'model' => $this->findModel($id),
    ]);
  }

  public function actionExport($id) {
    $sessionSet = $this->findModel($id);
    $sessionCollection = $sessionSet->sessionCollection;
    $frameCollection = $sessionCollection->frameCollection;

    $name = 'Set ' . $sessionSet->name;

    $doc = new WordExport('Export ' . $name);

    $doc->addTitle($name, 0);
    $doc->addText('This report has been exported on ' . Yii::$app->formatter->asDate(time(), 'dd-MM-yyyy') . '.');

    if ($sessionSet->description != null) {
      $doc->addText($sessionSet->description);
    }

    $doc->addTitle('General', 1);
    $doc->addInfoTable([
        'Nr devices' => $frameCollection->nrDevices,
        'Number of frames' => Yii::$app->formatter->asInteger($frameCollection->nrFrames)
    ]);

    // Coverage
    $doc->addTitle('Coverage', 1);
    $doc->addText('In this section the general LoRa performance is assessed.');
    $doc->addInfoTable([
        'Frame reception ratio' => $sessionCollection->frr['frr'],
        'Average RSSI' => $frameCollection->coverage->avgRssi . ' dBm',
        'Average SNR' => $frameCollection->coverage->avgSnr . ' dB',
        'Average ESP' => $frameCollection->coverage->avgEsp . ' dBm',
    ]);

    $doc->addTitle('Gateway count', 2);
    $doc->addText('The average gateway count is ' . $frameCollection->coverage->avgGwCount . '.');
    $doc->addColumnChart($frameCollection->coverage->gwCountPdf);

    $doc->addTitle('Spreading factor usage', 2);
    $info = $frameCollection->coverage->sfUsage;
    $values = [];
    $averageSf = 0;
    foreach ($info as $key => $value) {
      $values['SF' . $key] = $value;
      $averageSf += (($key * $value) / 100);
    }
    $doc->addText('The average spreading factor is SF' . (($averageSf == round($averageSf)) ? $averageSf : Yii::$app->formatter->asDecimal($averageSf, 1)));
    $doc->addColumnChart($values);

    $doc->addTitle('Channel occurrence', 2);
    $doc->addColumnChart($frameCollection->coverage->channelUsage);

    if ($frameCollection->geoloc->nrMeasurements === 0) {
      $doc->end();
    }
    $stats = $frameCollection->geoloc;

    $doc->addTitle('Geolocation', 1);
    $doc->addInfoTable([
        'Nr LocSolves' => Yii::$app->formatter->asDecimal($stats->nrLocalisations, 0)
    ]);

    $doc->addTitle('Accuracy', 2);
    $doc->addInfoTable([
        'Median accuracy' => Yii::$app->formatter->asDistance($stats->median),
        'Average accuracy' => Yii::$app->formatter->asDistance($stats->average),
        '2D accuracy' => Yii::$app->formatter->asDistance($stats->average2D['distance']) . ' ' . Frame::bearingText($stats->average2D['direction']),
        '90% of solves under' => Yii::$app->formatter->asDistance($stats->perc90point)
    ]);

    $doc->addText('Probability density function of the geolocation accuracy.');
    $doc->addColumnChart($stats->pdf);

    $doc->addTitle('Success rate', 2);
    $doc->addText('The Geolocation success rate is ' . Yii::$app->formatter->asPercent($stats->percentageNrLocalisations, 1) . '.');
    $tableCells = [
        ['GW Count', 'Frames', 'LocSolves', 'Success rate']
    ];
    foreach ($stats->perGatewayCount as $gwCount => $info) {
      $tableCells[] = [$gwCount, $info['count'], $info['locsolves'],
          ($info['count'] === 0) ? '-' : Yii::$app->formatter->asPercent($info['locsolves'] / $info['count'], 0)];
    }
    $doc->addTable($tableCells);


    $doc->end();
  }

  public function actionReportCoverage($id) {
    return $this->render('report-coverage', [
        'model' => $this->findModel($id),
    ]);
  }

  public function actionReportGeoloc($id) {
    return $this->render('report-geoloc', [
        'model' => $this->findModel($id),
    ]);
  }

  /**
   * Creates a new SessionSet model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate($session_ids = null) {
    $model = new SessionSet();
    if ($session_ids === null) {
      $newSessionIds = null;
    } else {
      $newSessionIds = explode('.', $session_ids);
    }

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      if ($newSessionIds !== null) {
        foreach ($newSessionIds as $sessionId) {
          $link = new SessionSetLink();
          $link->set_id = $model->id;
          $link->session_id = $sessionId;
          $link->save();
        }
      }
      return $this->redirect(['view', 'id' => $model->id]);
    } else {
      return $this->render('create', [
          'model' => $model,
          'nrNewSessions' => ($newSessionIds === null) ? null : count($newSessionIds)
      ]);
    }
  }

  public function actionAddSessions($id, $session_ids = null) {
    $model = $this->findModel($id);
    if ($session_ids === null) {
      $newSessionIds = null;
    } else {
      $newSessionIds = explode('.', $session_ids);
      $success = 0;
      foreach ($newSessionIds as $sessionId) {
        $link = new SessionSetLink();
        $link->set_id = $model->id;
        $link->session_id = $sessionId;
        if ($link->save()) {
          $success += 1;
        }
      }
      Yii::$app->session->addFlash('success', $success . ' sessions added to this set.');
    }
    return $this->redirect(['view', 'id' => $model->id]);
  }

  /**
   * Updates an existing SessionSet model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id) {
    $model = $this->findModel($id);

    $sessionRecords = Session::find()->with('device')->orderBy(['id' => SORT_DESC])->all();
    $sessions = [];
    $currentSessions = [];
    $modelSessions = $model->getSessions()->with(['device'])->all();
    foreach ($modelSessions as $currentSession) {
      $currentSessions[$currentSession->id] = $currentSession->fullName;
    }
    foreach ($sessionRecords as $record) {
      if (in_array($record->id, array_keys($currentSessions))) {
        continue;
      }
      $sessions[$record->id] = $record->fullName;
    }

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    } else {
      return $this->render('update', [
          'model' => $model,
          'sessions' => $sessions
      ]);
    }
  }

  /**
   * Deletes an existing SessionSet model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id) {
    $model = $this->findModel($id);
    SessionSetLink::deleteAll(['set_id' => $id]);
    $model->delete();

    return $this->redirect(['index']);
  }

  public function actionAddLink($from = null) {
    $session_id = Yii::$app->request->post('session_id');
    $set_id = Yii::$app->request->post('set_id');
    if ($session_id === null || $set_id === null) {
      throw new HttpException(404, 'Session or set not found');
    }

    $set = $this->findModel($set_id);
    $session = Session::findOne($session_id);
    if ($session === null) {
      throw new HttpException(404, 'Session not found');
    }

    $newLink = new SessionSetLink();
    $newLink->set_id = $set->id;
    $newLink->session_id = $session->id;
    $newLink->save();
    Yii::$app->session->addFlash('success', 'Session added to set ' . $set->name);

    switch ($from) {
      case 'set':
        return $this->redirect(['update', 'id' => $set_id]);
      case 'session':
      default:
        return $this->redirect(['/sessions/update', 'id' => $session_id]);
    }
  }

  public function actionDeleteLink($session_id, $set_id, $from = null) {
    $link = SessionSetLink::find()->andWhere(['session_id' => $session_id, 'set_id' => $set_id])->one();
    if ($link === null) {
      throw new HttpException(404, 'Link not found');
    }

    if ($link->delete()) {
      Yii::$app->session->addFlash('success', 'Link removed');
    } else {
      Yii::$app->session->addFlash('danger', 'Link could not be removed');
    }

    switch ($from) {
      case 'set':
        return $this->redirect(['update', 'id' => $set_id]);
      case 'session':
      default:
        return $this->redirect(['/sessions/update', 'id' => $session_id]);
    }
  }

  /**
   * Finds the SessionSet model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return SessionSet the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = SessionSet::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
