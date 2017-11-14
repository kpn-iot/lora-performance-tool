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

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle {

  public $basePath = '@webroot';
  public $baseUrl = '@web';
  public $css = [
    'css/yii-bootstrap.css',
    'css/site.css'
  ];
  public $js = [
  ];
  public $jsOptions = [
    'position' => \yii\web\View::POS_HEAD
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
  ];

}
