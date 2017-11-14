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

use app\components\Downlink;
use app\models\Device;
use yii\filters\AccessControl;
use yii\web\Controller;
use Yii;

/**
 * DataController implements the CRUD actions for Data model.
 */
class DataController extends Controller {

  public function beforeAction($action) {
    if ($action->id == 'downlink') {
      $this->enableCsrfValidation = false;
    }
    return parent::beforeAction($action);
  }

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
      ]
    ];
  }

  public function actionDownlink() {
    if (Yii::$app->request->isPost) {
      $settings = json_decode(file_get_contents("php://input"), true);
      $select = $settings['select'];

      $offset = 0;
      if (isset($select['offset']) && $select['offset'] != '') {
        $offset = (float) $select['offset'];
      }

      $device = Device::findOne($select['device']);
      $result = Downlink::thingpark($device, $select['payload'], $offset);
      die($result['description']);
    }

    return $this->render('downlink', [
        'devices' => array_map(function ($item) {
            return $item->attributes;
          }, Device::find()->orderBy('name')->all())
    ]);
  }

}
