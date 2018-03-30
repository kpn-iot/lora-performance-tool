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

namespace app\controllers;

use app\components\data\Ingestion;
use app\components\TokenVerification;
use app\models\ApiLog;
use app\models\Device;
use app\models\DeviceLocation;
use app\models\Frame;
use app\models\ReceptionRaw;
use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class ApiController extends Controller {

  /**
   * @inheritdoc
   */
  public function beforeAction($action) {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $this->enableCsrfValidation = false;
    return parent::beforeAction($action);
  }

  private static function error($code, $message, $logPayload = true) {
    ApiLog::log($message, $logPayload);
    throw new HttpException($code, $message);
  }

  public function actionThingpark() {
    // check POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      static::error(405, $_SERVER['REQUEST_METHOD'] . ' not allowed.');
    }

    // check JSON
    $requestBody = file_get_contents("php://input");
    $json = json_decode($requestBody);
    if ($json === null) {
      // check XML
      $in = simplexml_load_string($requestBody);
      if (!is_object($in)) {
        static::error(400, 'Body is no XML');
      }
      $messageType = $in->getName();
    } else {
      $messageType = key($json);
      $in = current($json);
    }

    // check Message name
    switch ($messageType) {
      case 'DevEUI_uplink':
        $this->_thingparkUplinkFrameIn($in);
        break;
      case 'DevEUI_location':
        static::error(204, 'Location data not implemented', false);
        break;
      case 'DevEUI_downlink_Sent':
        static::error(204, 'Downlink sent data not implemented', false);
        break;
      default:
        static::error(400, 'Message type ' . $messageType . ' not supported');
    }
  }

  private function _thingparkUplinkFrameIn($in) {
    if (!property_exists($in, 'DevEUI') || !property_exists($in, 'FPort')) {
      static::error(405, "No correct body, missing DevEUI or FPort");
    }

    // check Device
    $device = Device::find()->andWhere(['device_eui' => $in->DevEUI, 'port_id' => $in->FPort])->one();
    if ($device == null) {
      static::error(404, 'Device unknown.');
    }

    // check Token
    if ($device->lrc_as_key != null) {
        $verifier = new TokenVerification($device->lrc_as_key);
        if (!$verifier->checkUplinkToken(urldecode($_SERVER['QUERY_STRING']), $in)) {
            static::error(405, 'No correct token.');
        }
    }

    // ignore late messages
    if (property_exists($in, 'Late') && $in->Late == "1") {
      static::error(405, 'Late messages are ignored');
    }

    if (property_exists($in, 'Lrrid') && property_exists($in, 'LrrLAT') && property_exists($in, 'LrrLON')) {
      Ingestion::gateway((string) $in->Lrrid, (string) $in->LrrLAT, (string) $in->LrrLON);
    }

    // insert into DB
    $newFrame = new Frame();
    $newFrame->count_up = (string) $in->FCntUp;
    $newFrame->payload_hex = (string) $in->payload_hex;
    $newFrame->gateway_count = (int) $in->DevLrrCnt;
    $newFrame->channel = (string) $in->Channel;
    $newFrame->sf = (int) $in->SpFact;
    $newFrame->time = (string) $in->Time;

    if (isset($in->DevLAT)) {
      $previousFrameGeoloc = new DeviceLocation();
      $previousFrameGeoloc->latitude = (string) $in->DevLAT;
      $previousFrameGeoloc->longitude = (string) $in->DevLON;
      $previousFrameGeoloc->time = strtotime($in->DevLocTime);
      $previousFrameGeoloc->radius = (string) $in->DevLocRadius;
    } else {
      $previousFrameGeoloc = null;
    }

    $rawReceptions = [];
    if (property_exists($in, 'Lrrs')) {
      $receptions = json_decode(json_encode($in->Lrrs), true);
      if (isset($receptions['Lrr']['Lrrid'])) { //one gateway
        $receptions['Lrr'] = [$receptions['Lrr']];
      }
      foreach ($receptions['Lrr'] as $reception) {
        $newRawReception = new ReceptionRaw();
        $newRawReception->lrrId = $reception['Lrrid'];
        $newRawReception->rssi = (int) $reception['LrrRSSI'];
        $newRawReception->snr = (int) $reception['LrrSNR'];
        $newRawReception->esp = (float) $reception['LrrESP'];
        $rawReceptions[] = $newRawReception;
      }
    }

    Ingestion::frame($newFrame, $device, $previousFrameGeoloc, $rawReceptions);
    return "OK";
  }

}
