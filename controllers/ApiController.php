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
use app\components\LiveFeed;
use app\models\senml\SenMLPack;
use Yii;
use yii\db\Expression;
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

  private static function error($code, $message = null, $logPayload = true) {
    if ($message !== null) {
      ApiLog::log($message, $logPayload);
    }
    throw new HttpException($code, $message);
  }

  public function actionSenml() {
    // check POST
    if (!Yii::$app->request->isPost) {
      static::error(405, 'Only POST method allowed.');
    }

    $plugId = Yii::$app->request->headers->get("Things-Plug-UUID");
    $checkToken = Yii::$app->request->headers->get("Things-Message-Token");

    $body = Yii::$app->request->rawBody;

    try {
        $senMLPack = new SenMLPack($body);
    } catch (\Exception $e) {
        static::error(400, $e->getMessage());
    }

    /** @var Device $device */
    $device = Device::find()->andWhere(['device_eui' => $senMLPack->devEUI])->one();
    if ($device == null) {
      static::error(400, 'Device with ID ' . $senMLPack->devEUI . ' unknown');
    }

    // check Token
    if ($device->lrc_as_key != null) {
      $tokenData = $body.$device->lrc_as_key;
      $calcToken = hash('sha256', $tokenData);
      if ($checkToken !== $calcToken) {
        static::error(400, "Incorrect Things-Message-Token");
      }
    }

    if ($senMLPack->getMeasurement("counter") === null) {
      static::error(400, "Measurement with n='counter' is required!");
    }

    $formattedTime = Yii::$app->formatter->asDatetime($senMLPack->baseTime, "php:Y-m-d\TH:i:s.000P");

    // insert into DB
    $newFrame = new Frame();
    $newFrame->count_up = $senMLPack->getMeasurement("counter");
    $newFrame->payload_hex = null;
    $newFrame->information = $senMLPack->raw;

    if ($senMLPack->getMeasurement("latitude") !== null && $senMLPack->getMeasurement("longitude") !== null) {
      $newFrame->latitude = (string) $senMLPack->getMeasurement("latitude");
      $newFrame->longitude = (string) $senMLPack->getMeasurement("longitude");
    } else {
      $newFrame->latitude = null;
      $newFrame->longitude = null;
    }

    $newFrame->gateway_count = $senMLPack->getMeasurement("gatewayCount");
    $newFrame->channel = $senMLPack->getMeasurement("channel");
    $newFrame->sf = $senMLPack->getMeasurement("sf");
    $newFrame->location_radius_lora = $senMLPack->getMeasurement("locationRadius");
    $newFrame->time = $formattedTime;

    $previousFrameGeoloc = null;

    $rawReceptions = [];
    if ($senMLPack->getMeasurement("rssi") !== null || $senMLPack->getMeasurement("snr") !== null || $senMLPack->getMeasurement("esp") !== null) {
      $newRawReception = new ReceptionRaw();
      $newRawReception->lrrId = "LTE";
      $newRawReception->rssi = ($senMLPack->getMeasurement("rssi") === null) ? null : $senMLPack->getMeasurement("rssi");
      $newRawReception->snr = ($senMLPack->getMeasurement("snr") === null) ? null : $senMLPack->getMeasurement("snr");
      $newRawReception->esp = ($senMLPack->getMeasurement("esp") === null) ? null : $senMLPack->getMeasurement("esp");
      $rawReceptions[] = $newRawReception;
    }

    Ingestion::frame($newFrame, $device, $previousFrameGeoloc, $rawReceptions);

    Yii::$app->response->statusCode = 201;
    return;
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
      $key = "device-unknown-" . $in->DevEUI;
      $isLogged = \Yii::$app->cache->get($key);

      if ($isLogged === false) {
        \Yii::$app->cache->set($key, true, 60 * 60 * 24);
        static::error(404, 'Device unknown.', false);
      } else {
        static::error(404, null);
      }
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

  private function _thingparkGeoLocFrameIn($in) {
    $devEUI = (string) $in->DevEUI;
    $fCntUp = (string) $in->DevUlFCntUpUsed;

    // to make sure a geoloc frame arrives later than a uplink frame
    sleep(2);

    $loraLocation = new DeviceLocation();
    $loraLocation->latitude = (string) $in->DevLAT;
    $loraLocation->longitude = (string) $in->DevLON;
    $loraLocation->time = strtotime($in->DevLocTime);
    $loraLocation->radius = (string) $in->DevLocRadius;

    $algo = (string) $in->NwGeolocAlgo;
    $algorithmTranslation = ["0" => "tdoa", "1" => "rssi", "2" => "both"];
    $loraLocation->algorithm = (isset($algorithmTranslation[$algo])) ? $algorithmTranslation[$algo] : $algo;

    try {
      // check Device
      $device = Device::find()->andWhere(['device_eui' => $in->DevEUI])->one();
      if ($device === null) {
        return;
      }
    } catch (\Exception $e) {
      static::error(400, $e->getMessage());
    }

    $frame = Frame::find()->joinWith('device')
        ->where(['device_eui' => $devEUI, 'count_up' => $fCntUp])
        ->andWhere(new Expression("frames.created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 2 MINUTE)"))->one();
    if ($frame !== null) {
      $frame->saveLoRaLocation($loraLocation);
    } else {
      ApiLog::log("Could not find frame for geoloc, deveui={$devEUI}, fcntup={$fCntUp}", false);
    }

    $array = json_decode(json_encode($in), true);
    $array['type'] = 'location';
    LiveFeed::data($array);
  }

}
