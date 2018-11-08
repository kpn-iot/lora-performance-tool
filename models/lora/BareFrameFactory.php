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

use app\models\Gateway;

class BareFrameFactory {

  public $gatewayTranslation = [];

  public function __construct() {
    $gateways = Gateway::find()->select(['id', 'lrr_id', 'latitude', 'longitude'])->asArray()->all();
    foreach ($gateways as $gateway) {
      $this->gatewayTranslation[$gateway['id']] = $gateway;
    }
  }

  public function create($frame, $session) {
    return new BareFrame($frame, $session, $this);
  }

  public function getGatewayInfo($gatewayId) {
    if (isset($this->gatewayTranslation[$gatewayId])) {
      return $this->gatewayTranslation[$gatewayId];
    }
    return null;
  }

}