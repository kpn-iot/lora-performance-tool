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

use app\models\FrameSearch;
use app\models\Session;
use Yii;
use yii\base\ErrorException;
use yii\filters\AccessControl;
use yii\web\Controller;

class FramesController extends Controller {

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
      ]
    ];
  }

  /**
   * Lists all Data models.
   * @return mixed
   */
  public function actionIndex() {
    $searchModel = new FrameSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    try {
      $session_id = Yii::$app->request->queryParams['FrameSearch']['session_id'];
    } catch (ErrorException $e) {
      $session_id = null;
    }

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'session' => Session::findOne($session_id),
    ]);
  }

}
