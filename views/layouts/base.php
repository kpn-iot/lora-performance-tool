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

use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use app\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);

$this->beginContent('@app/views/layouts/empty.php');

NavBar::begin([
  'brandLabel' => Yii::$app->name,
  'brandUrl' => Yii::$app->homeUrl,
  'options' => [
    'class' => 'navbar-default navbar-fixed-top hidden-print',
  ],
]);

if (!Yii::$app->user->isGuest) {
  echo Nav::widget([
    'options' => ['class' => 'navbar-nav'],
    'items' => [
      ['label' => 'Live measurements', 'items' => [
          ['label' => 'Devices', 'url' => ['/devices']],
          ['label' => 'Session Sets', 'url' => ['/session-sets']],
          ['label' => 'Sessions', 'url' => ['/sessions']],
          ['label' => 'Frames', 'url' => ['/frames']],
          ['label' => 'GeoLoc Report', 'url' => ['/report']]
        ]
      ],
      ['label' => 'Quick measurements', 'url' => ['/quick/index']],
      ['label' => 'Locations', 'url' => ['/locations']],
      ['label' => 'Gateways', 'url' => ['/gateways']],
      ['label' => 'Tools', 'items' => [
          ['label' => 'Api log', 'url' => ['/log/index']],
          ['label' => 'User log', 'url' => ['/log/users']],
          ['label' => 'Downlink', 'url' => ['/data/downlink']],
          ['label' => 'Update payload decoding', 'url' => ['/site/payload-fixing']]
        ]
      ],
      ['label' => 'Map', 'url' => ['/map']]
    ],
  ]);

  echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
      ['label' => 'Logout', 'url' => ['/site/logout']]
    ]
  ]);
}
NavBar::end();

echo $content;

$this->endContent();
