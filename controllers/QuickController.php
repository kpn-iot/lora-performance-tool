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
use app\models\Quick;
use app\models\QuickSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\HttpException;

/**
 * QuickController implements the CRUD actions for Quick model.
 */
class QuickController extends Controller {

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
   * Lists all Quick models.
   * @return mixed
   */
  public function actionIndex() {
    $searchModel = new QuickSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
  }

  public function actionView($id) {
    return $this->redirect(['report-coverage', 'id' => $id]);
  }

  /**
   * Displays a single Quick model.
   * @param integer $id
   * @return mixed
   */
  public function actionReportCoverage($id) {
    $model = $this->findModel($id);

    return $this->render('report-coverage', [
        'model' => $model
    ]);
  }

  public function actionReportGeoloc($id) {
    $model = $this->findModel($id);

    return $this->render('report-geoloc', [
        'model' => $model
    ]);
  }

  /**
   * Creates a new Quick model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate() {
    $model = new Quick(['scenario' => 'create']);
    $model->type = 'static';

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    } else {
      return $this->render('create', [
          'model' => $model
      ]);
    }
  }

  /**
   * Updates an existing Quick model.
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

  public function actionFile($id) {
    $model = $this->findModel($id);
    $file = $model->filePath;

    $response = Yii::$app->response;
    $response->format = Response::FORMAT_RAW;

    $response->headers->set("Pragma", "public"); // required
    $response->headers->set("Expires", "0");
    $response->headers->set("Cache-Control", "max-age=172800, public, must-revalidate");
    $response->headers->set("Content-Type", "text/csv");

    $response->headers->set("Content-Disposition", "attachment; filename=\"{$model->name}.csv\";");
    $response->headers->set("Content-Transfer-Encoding", "binary");
    $response->headers->set("Content-Length", filesize($file));

    if (!is_resource($response->stream = fopen($file, 'r'))) {
      throw new HttpException(400, 'file access failed: permission deny');
    }

    $response->send();
  }

  /**
   * Deletes an existing Quick model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id) {
    $this->findModel($id)->delete();

    return $this->redirect(['index']);
  }

  /**
   * Finds the Quick model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Quick the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = Quick::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
