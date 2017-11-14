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

class MapAsset extends AssetBundle {

  public $basePath = '@webroot';
  public $baseUrl = '@web';
  public $js = [
    'js/functions.js',
    'js/app.js',
    'js/MapController.js'
  ];
  public $jsOptions = [
    'position' => \yii\web\View::POS_HEAD
  ];
  public $depends = [
    'app\assets\AppAsset',
    'app\assets\AngularAsset',
    'app\assets\AngularUiLeafletAsset',
    'app\assets\AngularGoogleChartAsset',
    'app\assets\NgstorageAsset'
  ];

}
