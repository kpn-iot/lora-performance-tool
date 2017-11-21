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

namespace app\components;

use app\models\Device;

class Downlink {

  static function thingpark(Device $device, $payload, $timestampOffset = 0) {
    $url = 'https://api.kpn-lora.com/thingpark/lrc/rest/downlink';

    $queryParameters = [
      'DevEUI' => $device->device_eui,
      'FPort' => $device->port_id,
      'Payload' => $payload,
      'AS_ID' => $device->as_id,
      'Time' => gmdate('Y-m-d\TH:i:s', time() + ((float) $timestampOffset)) //UTC time required
    ];


    $result = $queryParameters['Time'] . ": ";

    $queryString = '';
    foreach ($queryParameters as $key => $value) {
      $queryString .= $key . '=' . $value . '&';
    }
    $queryString = rtrim($queryString, '&');

    $hashIn = $queryString . $device->lrc_as_key;
    $token = hash('sha256', $hashIn);

    $postUrl = $url . '?' . $queryString . '&Token=' . $token;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $postUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'a=1');
    $response = curl_exec($ch);
    curl_close($ch);

    $success = (strpos($response, "Request queued by LRC") !== false);
    $result .= str_replace(['<html><body>', '</body></html>'], ['', ''], $response);

    return [
      'success' => $success,
      'description' => $result
    ];
  }

}
