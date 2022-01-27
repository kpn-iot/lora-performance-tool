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

use app\models\DeviceSearch;
use app\models\FrameSearch;
use app\models\lora\BareFrameFactory;
use app\models\lora\FrameCollection;
use app\models\Session;
use app\models\SessionSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ReportController implements the CRUD actions for Session model.
 */
class ReportController extends Controller {

  public function behaviors() {
    return [
        'access' => [
            'class' => \yii\filters\AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@']
                ]
            ]
        ]
    ];
  }

  /**
   * Lists all Session models.
   * @return mixed
   */
  public function actionIndex() {
    $deviceSearchModel = new DeviceSearch();

    $deviceIds = array_map(function ($data) {
      return $data['id'];
    }, $deviceSearchModel->filter(Yii::$app->request->queryParams)->select(['id'])->asArray()->all());

    $sessionSearchModel = new SessionSearch();
    $sessionRecords = $sessionSearchModel->filter(Yii::$app->request->queryParams, $deviceIds)->select(['id', 'location_report_source', 'type', 'latitude', 'longitude'])->asArray()->all();
    $sessions = [];
    $sessionIds = [];
    foreach ($sessionRecords as $session) {
      $sessionIds[] = $session['id'];
      $sessions[$session['id']] = $session;
    }
    unset($sessionRecords);

    $frameSearchModel = new FrameSearch();
    $frameSearchModel->createdAtMin = date('d-m-Y H:i', time() - (60 * 60 * 24 * 14));
    $frameSearchModel->createdAtMax = date('d-m-Y H:i');
    $frameSearchModel->gatewayCountMin = 3;
    $frameProvider = $frameSearchModel->filter(Yii::$app->request->queryParams, $sessionIds);
    $frameCount = $frameProvider->count();
    if ($frameCount < 50000 && $frameCount > 0) {
      $frames = [];
      $bareFrameFactory = new BareFrameFactory();
      $sessionIds = [];
      foreach ($frameProvider->with('reception')->asArray()->each() as $frame) {
        if (!in_array($frame['session_id'], $sessionIds)) {
          $sessionIds[] = $frame['session_id'];
        }
        $frames[] = $bareFrameFactory->create($frame, $sessions[$frame['session_id']]);
      }
      $frameCollection = new FrameCollection($frames);
      $sessionProvider = $sessionSearchModel->search(['SessionSearch' => ['idArray' => $sessionIds]]);
    } else {
      $frameCollection = null;
      $sessionProvider = null;
    }
    return $this->render('index', [
        'deviceSearchModel' => $deviceSearchModel,
        'sessionSearchModel' => $sessionSearchModel,
        'frameSearchModel' => $frameSearchModel,
        'sessionProvider' => $sessionProvider,
        'frameProvider' => $frameProvider,
        'frameCollection' => $frameCollection
    ]);
  }

  /**
   * Finds the Session model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param string $id
   * @return Session the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = Session::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
