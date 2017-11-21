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
use app\models\SessionSet;
use app\models\SessionSetSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\SessionSetLink;
use app\models\Session;

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
        'class' => \yii\filters\AccessControl::className(),
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
      throw new \yii\web\HttpException(404, 'Session or set not found');
    }

    $set = $this->findModel($set_id);
    $session = Session::findOne($session_id);
    if ($session === null) {
      throw new \yii\web\HttpException(404, 'Session not found');
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
      throw new \yii\web\HttpException(404, 'Link not found');
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
