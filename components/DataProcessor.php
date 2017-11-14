<?php

namespace app\components;

use app\models\Device;
use app\models\Frame;
use app\models\Gateway;
use app\models\Reception;
use app\models\Session;
use yii\web\HttpException;
use app\models\ApiLog;
use Yii;

class DataProcessor {

  private static function no($code, $message, $logPayload = true) {
    ApiLog::log($message, $logPayload);
    throw new HttpException($code, $message);
  }

  static $supportedPayloadTypes = [
      'adeunis' => 'Adeunis (only GPS)',
      'n1c2' => 'Streamline N1C2 (only GPS)',
      'marvin' => 'Marvin (no decoder)',
      'dent-marvin-gps' => 'Marvin GPS tracker'
  ];

  static function frameReceived($device, $in) {
    $newFrame = new Frame();
    $newFrame->count_up = (string) $in->FCntUp;
    $newFrame->payload_hex = (string) $in->payload_hex;
    $newFrame->gateway_count = (int) $in->DevLrrCnt;
    $newFrame->channel = (string) $in->Channel;
    $newFrame->sf = (int) $in->SpFact;
    $newFrame->time = (string) $in->Time;

    $session = Session::find()->andWhere(['device_id' => $device->id])->orderBy('created_at DESC')->one();
    $startNewSession = false;
    $copyOldSessionInfo = false;
    if ($session === null) {
      $startNewSession = true;
    } elseif ($session->lastFrame !== null) {
      if ($newFrame->count_up < $session->lastFrame->count_up || $newFrame->count_up == 0) { //new counter value is smaller than the previous frame
        $startNewSession = true;
      } elseif (strtotime(date('d-m-Y', strtotime($newFrame->time))) > strtotime(date('d-m-Y', strtotime($session->lastFrame->time)))) { // new frame received on a new day (after midnight)
        $startNewSession = true;
        $copyOldSessionInfo = true;
      }
    }

    if ($startNewSession) {
      if ($copyOldSessionInfo) {
        $previousSession = $session;
        $session = new Session();
        $session->device_id = $device->id;

        $re = '/ \(split[0-9\-a-zA-Z ]+\)/';
        $description = preg_replace($re, '', $previousSession->description);

        $session->description = $description . ' (split ' . Yii::$app->formatter->asDate($newFrame->time, 'E dd-MM') . ')';
        $session->type = $previousSession->type;
        $session->vehicle_type = $previousSession->vehicle_type;
        $session->motion_indicator = $previousSession->motion_indicator;
        $session->latitude = $previousSession->latitude;
        $session->longitude = $previousSession->longitude;
      } else {
        $session = new Session();
        $session->device_id = $device->id;
      }
      // drop frame if received already
    } elseif ($session->lastFrame != null && $newFrame->count_up == $session->lastFrame->count_up) {
      static::no(200, 'Frame is duplicate');
    }

    // store newly received lora localisation info
    if ($session->lastFrame != null && isset($in->DevLAT)) {
      $lastFrame = $session->lastFrame;
      $lastFrame->latitude_lora = (string) $in->DevLAT;
      $lastFrame->longitude_lora = (string) $in->DevLON;
      $lastFrame->location_age_lora = strtotime($lastFrame->time) - strtotime($in->DevLocTime);
      $lastFrame->save();
      // $lastFrame is used later on
    }
    $session->save();
    $newFrame->session_id = $session->id;

    $check = Frame::findOne(['session_id' => $session->id, 'count_up' => $newFrame->count_up]);
    if ($check !== null) {
      return $check;
    }

    $info = static::readPayload($session->device, $newFrame->payload_hex);
    if (isset($info['latitude']) && isset($info['longitude'])) {
      $newFrame->latitude = (string) $info['latitude'];
      $newFrame->longitude = (string) $info['longitude'];
      unset($info['latitude']);
      unset($info['longitude']);
    }
    $newFrame->information = $info;

    if (!$newFrame->save()) {
      static::no(500, json_encode($newFrame->errors));
    }

    $reception = json_decode(json_encode($in->Lrrs), true);
    if (isset($reception['Lrr']['Lrrid'])) { //one gateway
      $reception['Lrr'] = [$reception['Lrr']];
    }

    foreach ($reception['Lrr'] as $rec) {
      $gateway = Gateway::find()->where(['lrr_id' => $rec['Lrrid']])->one();
      $gatewayNotSaved = false;
      if ($gateway == null) {
        $gateway = new Gateway();
        $gateway->lrr_id = $rec['Lrrid'];
        $gateway->latitude = null;
        $gateway->longitude = null;
        if (!$gateway->save()) {
          ApiLog::log(json_encode($gateway->errors));
          $gatewayNotSaved = true;
        }
      }
      if ($gatewayNotSaved === false && $gateway->deleted_at === null) {
        $item = new Reception();
        $item->gateway_id = $gateway->id;
        $item->frame_id = $newFrame->id;
        $item->rssi = (int) $rec['LrrRSSI'];
        $item->snr = (int) $rec['LrrSNR'];
        $item->esp = (float) $rec['LrrESP'];
        if (!$item->save()) {
          ApiLog::log(json_encode($item->errors));
        }
      }
    }

    return $newFrame;
  }

  static function readPayload(Device $device, $payload) {
    $data = [];
    switch ($device->payload_type) {
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

  static function adeunis($payload, &$data) {
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

  static function n1c2($payload, &$data) {
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

  static function dentGps($payload, &$data) {
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
