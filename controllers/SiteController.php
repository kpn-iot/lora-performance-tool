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
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\UserLog;
use app\models\Session;
use app\models\ApiLog;
use app\components\data\Decoding;

class SiteController extends Controller {

  public function behaviors() {
    return [
      'access' => [
        'class' => AccessControl::className(),
        'rules' => [
          [
            'allow' => true,
            'roles' => ['@']
          ],
          [
            'actions' => ['login', 'error'],
            'allow' => true,
            'roles' => ['?']
          ]
        ],
      ]
    ];
  }

  public function actionError() {
    $exception = Yii::$app->errorHandler->exception;
    ApiLog::log([
      'error' => get_class($exception),
      'message' => $exception->getMessage(),
      'file' => $exception->getFile(),
      'line' => $exception->getLine(),
      'code' => $exception->getCode()
      ], false);
    if ($exception !== null) {
      return $this->render('error', [
          'exception' => $exception,
          'handler' => Yii::$app->errorHandler
      ]);
    }
  }

  public function actionIndex() {
    return $this->render('index', [
        'frontpageSessions' => Session::find()->orderBy(['p.last_frame_at' => SORT_DESC])->joinWith(['properties p', 'device'], true)->limit(4)->all()
    ]);
  }

  public function actionLogin() {
    if (!\Yii::$app->user->isGuest) {
      return $this->goHome();
    }

    $model = new LoginForm();
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
      return $this->goBack();
    } else {
      return $this->render('login', [
          'model' => $model,
      ]);
    }
  }

  public function actionLogout() {
    UserLog::log('logout');
    Yii::$app->user->logout();

    return $this->goHome();
  }

  public function actionPayloadFixing() {
    if (Yii::$app->request->isPost) {
      $info = Yii::$app->request->post('SessionSearch');
      if (!isset($info['id']) || $info['id'] == null) {
        $list = Yii::$app->request->post('list', '');
        if ($list != '') {
          $sessionIds = explode(',', $list);
        } else {
          return $this->refresh();
        }
      } else {
        $sessionIds = [$info['id']];
      }

      $logs = [];
      foreach ($sessionIds as $sessionId) {
        $session = Session::find()->with(['device', 'frames'])->andWhere(['id' => $sessionId])->one();
        if ($session === null) {
          continue;
        }

        foreach ($session->frames as $frame) {
          $info = Decoding::decode($frame->payload_hex, $session->device->payload_type);
          if (isset($info['latitude']) && isset($info['longitude'])) {
            $frame->latitude = (string) $info['latitude'];
            $frame->longitude = (string) $info['longitude'];
            unset($info['latitude']);
            unset($info['longitude']);
          } else {
            $frame->latitude = null;
            $frame->longitude = null;
          }
          $frame->information = $info;
          $frame->save();
        }
        $logs[] = $session->fullName;
      }

      Yii::$app->session->setFlash('success', count($logs) . ' session(s): ' . implode(', ', $logs) . ' has been decoded again!');
      return $this->refresh();
    }

    $sessionRecords = Session::find()->with(['device'])->orderBy(['created_at' => SORT_DESC])->all();
    $sessions = [];
    foreach ($sessionRecords as $record) {
      $sessions[$record->id] = $record->fullName;
    }

    return $this->render('payload-fixing', [
        'sessions' => $sessions
    ]);
  }

}
