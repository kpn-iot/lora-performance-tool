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
use app\models\ApiLog;
use app\models\ApiLogSearch;
use app\models\UserLogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * LogController implements the CRUD actions for ApiLog model.
 */
class LogController extends Controller {

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
   * Lists all ApiLog models.
   * @return mixed
   */
  public function actionIndex() {
    $searchModel = new ApiLogSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Displays a single ApiLog model.
   * @param string $id
   * @return mixed
   */
  public function actionView($id) {
    return $this->render('view', [
        'model' => $this->findModel($id),
    ]);
  }

  /**
   * Lists all UserLog models.
   * @return mixed
   */
  public function actionUsers() {
    $searchModel = new UserLogSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('users', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Finds the ApiLog model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param string $id
   * @return ApiLog the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = ApiLog::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
