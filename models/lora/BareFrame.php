<?php

/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2018 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

namespace app\models\lora;

use app\helpers\Calc;
use app\models\Session;

/**
 * Class BareFrame
 * @package app\models\lora
 *
 */
class BareFrame implements \ArrayAccess {

  public $channel, $timestamp, $device_eui, $location_age_lora, $gateway_count, $latitude, $longitude, $latitude_lora, $longitude_lora,
      $bearing, $count_up, $location_radius_lora, $distance, $sf, $reception = [], $time, $created_at;

  public function __construct($frameInfo, $session, BareFrameFactory &$factory) {
    $this->timestamp = strtotime($frameInfo['created_at'] . " UTC");
    $this->device_eui = '';

    foreach (['location_age_lora', 'latitude', 'longitude', 'latitude_lora', 'longitude_lora', 'gateway_count', 'count_up', 'location_radius_lora', 'sf', 'time', 'channel', 'created_at'] as $attr) {
      $this->$attr = $frameInfo[$attr];
    }

    foreach ($frameInfo['reception'] as $reception) {
      $gateway = $factory->getGatewayInfo($reception['gateway_id']);
      if ($gateway === null) {
        continue;
      }
      $reception['lrrId'] = $gateway['lrr_id'];

      if ($session['type'] == "static" && $session['latitude'] !== null && $session['longitude'] !== null) {
        $frameLat = $session['latitude'];
        $frameLon = $session['longitude'];
      } else {
        $frameLat = $this->latitude;
        $frameLon = $this->longitude;
      }
      if ($frameLat === null) {
        $distance = null;
      } elseif ($gateway['latitude'] == 0 || $gateway['longitude']) {
        $distance = null;
      } else {
        $distance = Calc::coordinateDistance($frameLat, $frameLon, $gateway['latitude'], $gateway['longitude']);
      }
      $reception['distance'] = $distance;

      $this->reception[] = $reception;
    }

    if ($session['type'] == "static" && $session['latitude'] !== null && $session['longitude'] !== null) {
      $latitude = $session['latitude'];
      $longitude = $session['longitude'];
    } else {
      $latitude = $this->latitude;
      $longitude = $this->longitude;
    }
    if (($latitude == 0 && $longitude == 0) || ($latitude == null && $longitude == null) || ($this->latitude_lora == null && $this->longitude_lora == null)) {
      $this->distance = null;
      $this->bearing = null;
    } else {
      $this->distance = Calc::coordinateDistance($latitude, $longitude, $this->latitude_lora, $this->longitude_lora);
      $this->bearing = Calc::coordinateBearing($latitude, $longitude, $this->latitude_lora, $this->longitude_lora);
    }
  }

  public function offsetExists($offset) {
    return isset($this->$offset);
  }

  public function offsetGet($offset) {
    return $this->$offset;
  }

  public function offsetSet($offset, $item) {
    $this->$offset = $item;
  }

  public function offsetUnset($offset) {
    $this->$offset = null;
  }
}