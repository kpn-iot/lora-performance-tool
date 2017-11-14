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

class AngularUiLeafletAsset extends AssetBundle {

  public $sourcePath = '@bower/ui-leaflet/dist';
  public $js = [
    'ui-leaflet.min.js'
  ];
  public $jsOptions = [
    'position' => \yii\web\View::POS_HEAD
  ];
  public $depends = [
    'app\assets\AngularAsset',
    'app\assets\AngularSimpleLoggerAsset',
    'app\assets\LeafletAsset'
  ];

}
