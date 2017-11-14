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

use yii\helpers\Html;

class Formatter extends \yii\i18n\Formatter {

  public function asList($value) {
    if (!is_array($value)) {
      return $value;
    }

    return $this->_makeList($value);
  }

  private function _makeList($list) {
    $ret = "<ul>";
    foreach ($list as $key => $value) {
      $ret .= "<li><b>{$key}: </b>" . ((is_array($value)) ? $this->_makeList($value) : $value) . "</li>";
    }
    $ret .= "</ul>";
    return $ret;
  }

  public function asTimeAgo($value, $config = null) {
    if ($value == null) {
      return '<i class="not-set">(not set)</i>';
    }

    $sec = time() - strtotime($this->asDatetime($value, 'php:d-m-Y H:i:s'));

    if ($sec < 60) {
      return 'less than a minute ago';
    }
    $min = round($sec / 60);
    if ($min < 60) {
      return $min . ' ' . (($min == 1) ? 'minute' : 'minutes') . ' ago';
    }
    $hour = round($sec / (60 * 60));
    if ($hour < 24) {
      return $hour . ' ' . (($hour == 1) ? 'hour' : 'hours') . ' ago';
    }
    $day = round($sec / (60 * 60 * 24));
    if ($day < 14) {
      return $day . ' ' . (($day == 1) ? 'day' : 'days') . ' ago';
    }
    $week = round($sec / (60 * 60 * 24 * 7));
    return $week . ' ' . (($week == 1) ? 'week' : 'weeks') . ' ago';
  }

  public function asCoordinates($value, $config = null) {
    $parts = explode(',', $value);
    if (count($parts) == 1) {
      return $value;
    }
    $lat = round(trim($parts[0]), 6);
    $lon = round(trim($parts[1]), 6);
    $value = $lat . ', ' . $lon;
    $noSpace = $lat . ',' . $lon;

    if ($lat === 0 && $lon === 0) {
      return $value;
    } else if ($lat == null || $lon == null) {
      return null;
    }

    return Html::a($value, 'https://www.google.nl/maps/search/' . $noSpace, ['target' => '_blank']);
  }

}
