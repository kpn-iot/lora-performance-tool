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

class LeafletAsset extends AssetBundle {

  public $sourcePath = '@bower/leaflet/dist';
  public $js = [
    'leaflet.js'
  ];
  public $css = [
    'leaflet.css'
  ];
  public $jsOptions = [
    'position' => \yii\web\View::POS_HEAD
  ];

}
