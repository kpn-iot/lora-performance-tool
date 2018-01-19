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

class ArrayHelper extends \yii\helpers\ArrayHelper {

  public static function getAverage($arr) {
    if (!count($arr)) {
      return 0;
    }

    $sum = 0;
    for ($i = 0; $i < count($arr); $i++) {
      $sum += $arr[$i];
    }

    return $sum / count($arr);
  }

  public static function getVariance($arr) {
    if (count($arr) < 2) {
      return 0;
    }

    $mean = static::getAverage($arr);

    $sos = 0;    // Sum of squares
    for ($i = 0; $i < count($arr); $i++) {
      $sos += ($arr[$i] - $mean) * ($arr[$i] - $mean);
    }

    return $sos / (count($arr) - 1);  // denominator = n-1; i.e. estimating based on sample
    // n-1 is also what MS Excel takes by default in the
    // VAR function
  }

  //put your code here
}
