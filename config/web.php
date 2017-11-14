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

$params = require(__DIR__ . '/params.php');

$config = [
  'id' => 'performance-tool',
  'name' => 'Performance Tool',
  'basePath' => dirname(__DIR__),
  'timeZone' => 'Europe/Amsterdam',
  'language' => 'en',
  'bootstrap' => ['log'],
  'components' => [
    'request' => [
      // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
      'cookieValidationKey' => '',
    ],
    'cache' => [
      'class' => 'yii\caching\FileCache',
    ],
    'user' => [
      'identityClass' => 'app\models\User',
      'enableAutoLogin' => true,
    ],
    'errorHandler' => [
      'errorAction' => 'site/error',
    ],
    'mailer' => [
      'class' => 'yii\swiftmailer\Mailer',
      // send all mails to a file by default. You have to set
      // 'useFileTransport' to false and configure a transport
      // for the mailer to send real emails.
      'useFileTransport' => true,
    ],
    'log' => [
      'traceLevel' => YII_DEBUG ? 3 : 0,
      'targets' => [
        [
          'class' => 'yii\log\FileTarget',
          'levels' => ['error', 'warning'],
        ],
      ],
    ],
    'urlManager' => [
      'enablePrettyUrl' => true,
      'showScriptName' => false,
      'rules' => [
        'device/<id:\d+>' => 'devices/view',
        'device/<id:\d+>/update' => 'devices/update',
        'session/geoloc/<id:[0-9\.]+>' => 'sessions/report-geoloc',
        'session/update/<id:\d+>' => 'sessions/update',
        'session/<id:[0-9\.]+>' => 'sessions/report-coverage',
        'session/coverage/<id:[0-9\.]+>' => 'sessions/report-coverage',
        'session/map/<session_id:[0-9\.]+>' => 'map/index',
        'quick/geoloc/<id:[0-9\.]+>' => 'quick/report-geoloc',
        'quick/update/<id:\d+>' => 'quick/update',
        'quick/<id:[0-9\.]+>' => 'quick/report-coverage',
        'set/<id:\d+>' => 'session-sets/view',
        'set/update/<id:\d+>' => 'session-sets/update',
        'set/coverage/<id:\d+>' => 'session-sets/report-coverage',
        'set/geoloc/<id:\d+>' => 'session-sets/report-geoloc'
      ]
    ],
    'db' => require(__DIR__ . '/db.php'),
    'formatter' => [
      'class' => 'app\helpers\Formatter',
      'datetimeFormat' => 'php:D d-m-Y H:i'
    ]
  ],
  'params' => $params,
];

if (YII_ENV_DEV) {
  // configuration adjustments for 'dev' environment
  $config['bootstrap'][] = 'debug';
  $config['modules']['debug'] = 'yii\debug\Module';

  $config['bootstrap'][] = 'gii';
  $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
