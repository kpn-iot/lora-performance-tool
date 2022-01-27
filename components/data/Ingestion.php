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

use app\models\Device;
use app\models\Frame;
use app\models\DeviceLocation;
use app\models\Gateway;
use app\models\ReceptionRaw;
use app\models\Reception;
use app\models\Session;
use yii\web\HttpException;
use app\models\ApiLog;
use Yii;

class Ingestion {

  private static function error($code, $message, $logPayload = true) {
    ApiLog::log($message, $logPayload);
    throw new HttpException($code, $message);
  }

  public static function gateway($lrrId, $latitude = null, $longitude = null) {
    // build gateway database
    $gateway = Gateway::find()->where(['lrr_id' => $lrrId])->one(); //get also deleted
    if ($gateway === null) { //if gateway not in gateway list
      $gateway = new Gateway();
      $gateway->lrr_id = $lrrId;
      $gateway->latitude = $latitude;
      $gateway->longitude = $longitude;
      $gateway->save();
    } elseif ($gateway->deleted_at !== null) { //if gateway was soft deleted
      $gateway->deleted_at = null;
      $gateway->latitude = $latitude;
      $gateway->longitude = $longitude;
      $gateway->save();
    } else {
      $gateway->touch('updated_at');
      $gateway->save();
    }
  }

  /**
   *
   * @param Frame $newFrame
   * @param Device $device
   * @param DeviceLocation|null $previousFrameGeoloc
   * @param ReceptionRaw[]|null $receptions
   * @return Frame
   * @throws HttpException
   */
  public static function frame($newFrame, $device, $previousFrameGeoloc = null, $receptions = null) {
    /** @var Session $session */
    $session = Session::find()->with('device')->andWhere(['device_id' => $device->id])->orderBy('created_at DESC')->one();
    $startNewSession = false;
    $copyOldSessionInfo = false;
    if ($session === null) {
      $startNewSession = true;
    } elseif ($session->lastFrame !== null) {
      if ($newFrame->count_up < $session->lastFrame->count_up || $newFrame->count_up == 0) { //new counter value is smaller than the previous frame
        $startNewSession = true;
      } elseif ($session->device->autosplit && (strtotime(date('d-m-Y', strtotime($newFrame->time))) > strtotime(date('d-m-Y', strtotime($session->lastFrame->time))))) { // new frame received on a new day (after midnight)
        $startNewSession = true;
        $copyOldSessionInfo = true;
      } elseif ($session->properties !== null && $session->properties->nr_frames >= 1000) {
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
        $session->location_id = $previousSession->location_id;
        $session->latitude = $previousSession->latitude;
        $session->longitude = $previousSession->longitude;
        $session->location_report_source = $previousSession->location_report_source;
      } else {
        $session = new Session();
        $session->device_id = $device->id;
      }
      // drop frame if received already
    } elseif ($session->lastFrame != null && $newFrame->count_up == $session->lastFrame->count_up) {
      static::error(409, 'Frame is duplicate');
    }

    // store newly received lora localisation info
    if ($previousFrameGeoloc !== null && $session->lastFrame != null) {
      $lastFrame = $session->lastFrame;
      $lastFrame->saveLoRaLocation($previousFrameGeoloc);
      // $lastFrame is used later on
    }
    $session->save();
    $newFrame->session_id = $session->id;

    $check = Frame::findOne(['session_id' => $session->id, 'count_up' => $newFrame->count_up]);
    if ($check !== null) {
      return $check;
    }

    // decoding if payload hex is set
    if ($newFrame->payload_hex !== null) {
      $info = Decoding::decode($newFrame->payload_hex, $session->device->payload_type);
      if (isset($info['latitude']) && isset($info['longitude'])) {
        $newFrame->latitude = (string)$info['latitude'];
        $newFrame->longitude = (string)$info['longitude'];
        unset($info['latitude']);
        unset($info['longitude']);
      }
      $newFrame->information = $info;
    }

    if (!$newFrame->save()) {
      static::error(500, json_encode($newFrame->errors));
    }

    foreach ($receptions as $reception) {
      $gateway = Gateway::find()->where(['lrr_id' => $reception->lrrId])->one();
      $gatewayNotSaved = false;
      if ($gateway == null) {
        $gateway = new Gateway();
        $gateway->lrr_id = $reception->lrrId;
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
        $item->rssi = (int) $reception->rssi;
        $item->snr = (int) $reception->snr;
        $item->esp = (double) $reception->esp;
        if (!$item->save()) {
          ApiLog::log(json_encode($item->errors));
        }
      }
    }
    
    $session->updateProperties();

    $data = [
      'frame' => $newFrame->getAttributes(['id', 'count_up', 'payload_hex', 'latitude', 'longitude', 'gateway_count', 'channel', 'sf', 'informationArray', 'time']),
      'session' => $session->getAttributes(['id']),
      'device' => $session->device->getAttributes(['id', 'name'])
    ];

    $data['reception'] = [];
    foreach ($newFrame->reception as $reception) {
      $newRec = $reception->getAttributes(['rssi', 'snr', 'esp', 'distance']);
      $newRec['gateway'] = $reception->gateway->getAttributes(['latitude', 'longitude', 'lrr_id']);
      $data['reception'][] = $newRec;
    }

    if (isset($lastFrame)) {
      $data['lastFrame'] = $lastFrame->getAttributes(['id', 'latitude_lora', 'longitude_lora', 'location_age_lora', 'distance']);
    }

    $data['type'] = 'data';
    return $newFrame;
  }

}
