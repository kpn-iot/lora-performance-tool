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

class AngularSimpleLoggerAsset extends AssetBundle {

  public $sourcePath = '@bower/angular-simple-logger/dist';
  public $js = [
    'angular-simple-logger.js'
  ];
  public $jsOptions = [
    'position' => \yii\web\View::POS_HEAD
  ];

}
