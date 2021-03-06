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

namespace app\models;

use Yii;
use yii\helpers\Html;
use app\helpers\Calc;

/**
 * This is the model class for table "frames".
 *
 * @property string $id
 * @property string $session_id
 * @property string $count_up
 * @property string $payload_hex
 * @property string $information
 * @property string $latitude
 * @property string $longitude
 * @property integer $gateway_count
 * @property string $channel
 * @property integer $sf
 * @property string $time
 * @property string $latitude_lora
 * @property string $longitude_lora
 * @property integer $location_age_lora
 * @property string $location_radius_lora
 * @property integer $distance
 * @property integer $bearing
 * @property string $created_at
 * @property string $updated_at
 * @property integer $timestamp
 *
 * @property Session $session
 */
class Frame extends ActiveRecord {

  public static $locationAgeThreshold = 5;
  public $count, $date, $distanceRounded;
  private $_timestamp = null;

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'frames';
  }

  public function beforeValidate() {
    if (is_array($this->information)) {
      $this->information = json_encode($this->information);
    }
    if ($this->information == "[]") {
      $this->information = null;
    }

    return parent::beforeValidate();
  }

  public function getInformationArray() {
    if (is_array($this->information) || $this->information === null) {
      return $this->information;
    } else {
      return json_decode($this->information, true);
    }
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['session_id', 'count_up'], 'required'],
      [['session_id', 'count_up', 'gateway_count', 'location_age_lora', 'sf'], 'integer'],
      [['payload_hex', 'information', 'channel'], 'string'],
      [['created_at', 'updated_at', 'latitude_lora', 'longitude_lora'], 'safe'],
      [['latitude', 'longitude'], 'string', 'max' => 15],
      [['time'], 'string', 'max' => 100],
      [['session_id'], 'exist', 'skipOnError' => true, 'targetClass' => Session::className(), 'targetAttribute' => ['session_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'session_id' => 'Session ID',
      'count_up' => 'Count Up',
      'payload_hex' => 'Payload Hex',
      'information' => 'Information',
      'latitude' => 'Latitude',
      'longitude' => 'Longitude',
      'gateway_count' => 'Gateway Count',
      'channel' => 'Channel',
      'sf' => 'Spreading Factor',
      'time' => 'Time',
      'latitude_lora' => 'Latitude Lora',
      'longitude_lora' => 'Longitude Lora',
      'location_age_lora' => 'Location Age Lora',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  public function getDevice_eui() {
    return $this->session->device->device_eui;
  }

  public function getTimestamp() {
    if ($this->_timestamp === null) {
      $this->_timestamp = strtotime($this->created_at . " UTC");
    }
    return $this->_timestamp;
  }

  public function getCoordinates() {
    if ($this->session->type == "static" && $this->session->latitude !== null && $this->session->longitude !== null) {
      return Html::tag('i', "Static");
    }

    if (($this->latitude == 0 && $this->longitude == 0) || ($this->latitude == null && $this->longitude == null)) {
      return Html::tag('i', '(not set)', ['class' => 'not-set']);
    }
    return Yii::$app->formatter->asCoordinates($this->latitude . ', ' . $this->longitude);
  }

  public function getLoraCoordinates() {
    if ($this->latitude_lora == null && $this->longitude_lora == null) {
      return Html::tag('i', '(not set)', ['class' => 'not-set']);
    }
    return Yii::$app->formatter->asCoordinates($this->latitude_lora . ', ' . $this->longitude_lora);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSession() {
    return $this->hasOne(Session::className(), ['id' => 'session_id']);
  }

  public function getLatDiff() {
    if ($this->session->type == "static" && $this->session->latitude !== null && $this->session->longitude !== null) {
      $latitude = $this->session->latitude;
    } else {
      $latitude = $this->latitude;
    }
    return $latitude - $this->latitude_lora;
  }

  public function getLonDiff() {
    if ($this->session->type == "static" && $this->session->latitude !== null && $this->session->longitude !== null) {
      $longitude = $this->session->longitude;
    } else {
      $longitude = $this->longitude;
    }
    return $longitude - $this->longitude_lora;
  }

  public function getDistance() {
    if ($this->session->type == "static" && $this->session->latitude !== null && $this->session->longitude !== null) {
      $latitude = $this->session->latitude;
      $longitude = $this->session->longitude;
    } else {
      $latitude = $this->latitude;
      $longitude = $this->longitude;
    }
    if (($latitude == 0 && $longitude == 0) || ($latitude == null && $longitude == null) || ($this->latitude_lora == null && $this->longitude_lora == null)) {
      return null;
    }
    return Calc::coordinateDistance($latitude, $longitude, $this->latitude_lora, $this->longitude_lora);
  }

  public function getBearing() {
    if ($this->session->type == "static" && $this->session->latitude !== null && $this->session->longitude !== null) {
      $latitude = $this->session->latitude;
      $longitude = $this->session->longitude;
    } else {
      $latitude = $this->latitude;
      $longitude = $this->longitude;
    }
    if (($latitude == 0 && $longitude == 0) || ($latitude == null && $longitude == null) || ($this->latitude_lora == null && $this->longitude_lora == null)) {
      return null;
    }
    return Calc::coordinateBearing($latitude, $longitude, $this->latitude_lora, $this->longitude_lora);
  }

  public function getBearingArrow() {
    return static::formatBearingArrow($this->bearing);
  }

  public static function bearingText($bearing) {
    return static::_bearing($bearing, [
        0 => "N",
        45 => "NE",
        90 => "E",
        135 => "SE",
        180 => "S",
        225 => "SW",
        270 => "W",
        315 => "NW"
    ]);
  }

  public static function formatBearingArrow($bearing) {
    return static::_bearing($bearing, [
        0 => "&uarr;",
        45 => "&nearr;",
        90 => "&rarr;",
        135 => "&searr;",
        180 => "&darr;",
        225 => "&swarr;",
        270 => "&larr;",
        315 => "&nwarr;"
    ]);
  }

  private static function _bearing($bearing, $degrees) {
	  if ($bearing === null) {
		  return null;
	  }
    foreach ($degrees as $deg => $arrow) {
      $min = (360 + $deg - 22.5) % 360;
      $max = ($deg + 22.5) % 360;
      if (($deg === 0 && ($bearing >= $min || $bearing < $max)) || ($deg !== 0 && ($bearing >= $min && $bearing < $max))) {
        return $arrow;
      }
    }
    return null;
  }

  public function getReception() {
    return $this->hasMany(Reception::className(), ['frame_id' => 'id'])->orderBy(['rssi' => SORT_DESC]);
  }

  public function getReceptionInfo() {
    $out = "<table class='table table-bordered' style='margin:0'>";
    foreach ($this->reception as $reception) {
      if ($this->latitude === null) {
        $distance = null;
      } elseif ($reception->gateway->latitude == 0) {
        $distance = null;
      } else {
        $distance = Calc::coordinateDistance($this->latitude, $this->longitude, $reception->gateway->latitude, $reception->gateway->longitude);
      }
      $out .= "<tr><td>" . Html::a($reception->gateway->lrr_id, ['/gateways/view', 'id' => $reception->gateway_id]) . "</td>"
        . "<td class='text-right'>" . $distance . "</td>"
        . "<td class='text-right'>" . $reception->rssi . "</td>"
        . "<td class='text-right'>" . $reception->snr . "</td>"
        . "<td class='text-right'>" . $reception->esp . "</td></tr>";
    }
    return $out . "</table>";
  }

}
