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

use app\models\Gateway;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use app\models\Session;
use app\models\lora\SessionCollection;

class MapController extends Controller {

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
    ];
  }

  public function actionIndex($session_id = null) {
    return $this->render('index', ['session_id' => $session_id]);
  }

  public function actionPopover($session_id = null) {
    return $this->render('popover', ['session_id' => $session_id]);
  }

  public function actionSmall($session_id = null) {
    return $this->render('small', ['session_id' => $session_id]);
  }

  public function actionGateways() {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return array_map(function ($item) {
      return [
        'name' => $item->lrr_id,
        'type' => $item->type,
        'latitude' => $item->latitude,
        'longitude' => $item->longitude
      ];
    }, Gateway::find()->all());
  }

  public function actionFrames($session_id) {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $sessionIds = explode('.', $session_id);
    $sessions = Session::find()->with(['frames', 'device', 'frames.reception', 'frames.reception.gateway'])->andWhere(['id' => $sessionIds])->all();
    $sessionCollection = new SessionCollection($sessions);

    return [
      'name' => $sessionCollection->name,
      'frames' => $sessionCollection->frameCollection->mapData->data
    ];
  }

}
