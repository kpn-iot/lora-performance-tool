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

namespace app\components\data;

class Decoding {

  static private $_supportedPayloadTypes = [
    'adeunis' => 'Adeunis (only GPS)',
    'n1c2' => 'Streamline N1C2 (only GPS)',
    'marvin' => 'Marvin (no decoder)',
    'dent-marvin-gps' => 'Marvin GPS tracker'
  ];

  public static function getSupportedPayloadTypes() {
    return self::$_supportedPayloadTypes;
  }

  public static function decode($payload, $payloadType) {
    $data = [];
    switch ($payloadType) {
      case 'adeunis':
        static::adeunis($payload, $data);
        break;
      case 'n1c2':
        static::n1c2($payload, $data);
        break;
      case 'dent-marvin-gps':
        static::dentGps($payload, $data);
        break;
    }
    return $data;
  }

  /**
   * Decoder for the Adeunis LoRaWAN Field Test Device.
   * Only decodes the GPS-part of the payload
   */
  public static function adeunis($payload, &$data) {
    if ((hexdec(substr($payload, 0, 1)) & 1) && (strlen($payload) >= 19)) {
      $data['latitude'] = hexdec($payload[4]) * 10 +
        hexdec($payload[5]) + (
        hexdec($payload[6]) * 10 +
        hexdec($payload[7]) +
        hexdec($payload[8]) / 10 +
        hexdec($payload[9]) / 100 +
        hexdec($payload[10]) / 1000
        ) / 60;
      $data['longitude'] = hexdec($payload[12]) * 100 +
        hexdec($payload[13]) * 10 +
        hexdec($payload[14]) + (
        hexdec($payload[15]) * 10 +
        hexdec($payload[16]) +
        hexdec($payload[17]) / 10 +
        hexdec($payload[18]) / 100
        ) / 60;
    }
  }

  /**
   * Decoder for KCS TraceME TM-901 / N1C2
   * Only decoded the GPS-part of the payload
   */
  public static function n1c2($payload, &$data) {
    $lat = hexdec(substr($payload, 18, 8));
    if ($lat & 0x80000000) {
      $lat -= (0x100000000);
    }
    $lat /= 600000;

    $lon = hexdec(substr($payload, 10, 8));
    if ($lon & 0x80000000) {
      $lon -= (0x100000000);
    }
    $lon /= 600000;

    $data['latitude'] = round($lat, 5);
    $data['longitude'] = round($lon, 5);
  }

  /**
   * Decoder for a proprietary GPS-tracking solution based on a Marvin LoRa development board
   */
  public static function dentGps($payload, &$data) {
    $re = '/([0-9]+)[cC]{1}([0-9]+)[fF]{1}([0-9]+)/';

    preg_match_all($re, $payload, $matches, PREG_SET_ORDER, 0);
    if (count($matches) == 0) {
      return;
    }

    $data['counter'] = (int) $matches[0][1];
    $data['latitude'] = ((int) $matches[0][2]) / 1000000;
    $data['longitude'] = ((int) $matches[0][3]) / 1000000;
  }

}
