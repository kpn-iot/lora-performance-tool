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

  /**
   * Calculate token and queue a downlink message
   * 
   * @param Device $device
   * @param type $payload
   * @param type $timestampOffset
   * @return array
   */
  static function thingpark(Device $device, $payload, $timestampOffset = 0, $overwritePort = null, $confirmed = false, $sendToPartnerNetwork = false) {
    if ($sendToPartnerNetwork) {
		$url = 'https://api-dev1.thingpark.com/thingpark/lrc/rest/downlink';
	} else {
		$url = 'https://api.kpn-lora.com/thingpark/lrc/rest/downlink';
	}

    $queryParameters = [
      'DevEUI' => $device->device_eui,
      'FPort' => ($overwritePort === null) ? $device->port_id : $overwritePort,
      'Payload' => $payload,
      'Confirmed' => ($confirmed) ? 1 : 0,
      'Time' => gmdate('Y-m-d\TH:i:s', time() + ((float) $timestampOffset)) //UTC time required
    ];
	
	if (!$sendToPartnerNetwork) {
		$queryParameters['AS_ID'] = $device->as_id;
	}

    $result = $queryParameters['Time'] . ": ";

    $queryString = '';
    foreach ($queryParameters as $key => $value) {
      $queryString .= $key . '=' . $value . '&';
    }
    $queryString = rtrim($queryString, '&');
    $postUrl = $url . '?' . $queryString;

	if (!$sendToPartnerNetwork) {		
		$hashIn = $queryString . strtolower($device->lrc_as_key);
		$token = hash('sha256', $hashIn);
		$postUrl .= '&Token=' . $token;
	}
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $postUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'a=1');
    $response = curl_exec($ch);

    if ($response === false) {
      $success = false;
      $result = "CURL error: " . curl_error($ch);
    } else {
      $success = (strpos($response, "Request queued by LRC") !== false);
      $result .= str_replace(['<html><body>', '</body></html>'], ['', ''], $response);
    }

    curl_close($ch);

    return [
      'success' => $success,
      'description' => $result
    ];
  }

}
