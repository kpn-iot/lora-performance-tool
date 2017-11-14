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

namespace app\helpers;

use app\assets\FontAwesomeAsset;
use Yii;

class Html extends \yii\bootstrap\Html {

  public static $fontAwesomeAssetRegistered = false;

  public static function fa($name, $size = 1) {
    if (!static::$fontAwesomeAssetRegistered) {
      FontAwesomeAsset::register(Yii::$app->getView());
      static::$fontAwesomeAssetRegistered = true;
    }

    $class = 'fa fa-' . $name;
    if ($size > 1) {
      $class .= ' fa-' . round($size) . 'x';
    }
    return static::tag('i', '', ['class' => $class]);
  }

}
