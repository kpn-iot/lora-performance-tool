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
use app\helpers\Html;
use app\models\Device;
use app\models\forms\SessionMergeForm;
use app\models\Frame;
use app\models\lora\SessionCollection;
use app\models\Session;
use app\models\SessionSearch;
use app\models\SessionSet;
use app\models\SessionSplitForm;
use Yii;
use yii\base\ErrorException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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
                'bulk-delete' => ['POST'],
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
        'sessionSets' => array_map(function ($item) {
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

  public function actionMerge() {
    $sessionMerge = new SessionMergeForm();

    if ($sessionMerge->load(Yii::$app->request->post()) && $sessionMerge->validate()) {
      $sessionMerge->execute();
      Yii::$app->session->setFlash('success', 'Sessions have been merged. ' . Html::a('Go to the freshly merged session', ['view', 'id' => $sessionMerge->targetSessionId]) . '.');
      return $this->refresh();
    }

    return $this->render('merge', [
        'sessionMerge' => $sessionMerge
    ]);
  }

  public function actionExport($id) {
    /** @var Session $session */
    $session = $this->sessionBaseFind()->andWhere(['id' => $id])->one();

    $doc = new WordExport('Export ' . $session->name);

    $doc->addTitle($session->name, 0);
    $doc->addText('This report has been exported on ' . Yii::$app->formatter->asDate(time(), 'dd-MM-yyyy') . '.');

    $doc->addTitle('General', 1);
    $doc->addInfoTable([
        'Device' => $session->device->name,
        'Motion indicator' => $session->motionIndicatorReadable,
        'Measurement type' => $session->typeReadable,
        'Vehicle type' => $session->vehicleTypeReadable,
        'Number of frames' => Yii::$app->formatter->asInteger($session->nrFrames),
        'Frame counter range' => $session->countUpRange,
        'First frame at' => Yii::$app->formatter->asDatetime($session->prop->first_frame_at),
        'Last frame at' => Yii::$app->formatter->asDatetime($session->prop->last_frame_at),
        'Runtime of test' => $session->runtime,
    ]);

    // Coverage
    $doc->addTitle('Coverage', 1);
    $doc->addText('In this section the general LoRa performance is assessed.');
    $doc->addInfoTable([
        'Frame reception ratio' => $session->frr,
        'Used spreading factor' => $session->sf,
        'Average RSSI' => $session->prop->rssi_average . ' dBm',
        'Average SNR' => $session->prop->snr_average . ' dB',
        'Average ESP' => $session->prop->esp_average . ' dBm',
    ]);

    $doc->addTitle('Gateway count', 2);
    $doc->addText('The average gateway count is ' . $session->avgGwCount . '.');
    $doc->addColumnChart($session->frameCollection->coverage->gwCountPdf);

    $doc->addTitle('Spreading factor usage', 2);
    $info = $session->frameCollection->coverage->sfUsage;
    $values = [];
    $averageSf = 0;
    foreach ($info as $key => $value) {
      $values['SF' . $key] = $value;
      $averageSf += (($key * $value) / 100);
    }
    $doc->addText('The average spreading factor is SF' . (($averageSf == round($averageSf)) ? $averageSf : Yii::$app->formatter->asDecimal($averageSf, 1)));
    $doc->addColumnChart($values);

    $doc->addTitle('Channel occurrence', 2);
    $doc->addColumnChart($session->frameCollection->coverage->channelUsage);

    if ($session->frameCollection->geoloc->nrMeasurements === 0) {
      $doc->end();
    }
    $stats = $session->frameCollection->geoloc;

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

  public function actionBulkDelete() {
    $ids = Yii::$app->request->post('ids');
    if (count($ids) > 20) {
      throw new \Exception("You cannot bulk delete more than 20 sessions at once");
    }
    foreach ($ids as $id) {
      $this->findModel($id)->delete();
    }
    return;
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
