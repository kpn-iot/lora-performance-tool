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
use app\helpers\Calc;

/**
 * This is the model class for table "reception".
 *
 * @property string $frame_id
 * @property integer $gateway_id
 * @property integer $rssi
 * @property integer $snr
 * @property double $esp
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Frames $frame
 * @property Gateways $gateway
 */
class Reception extends ActiveRecord {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'reception';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['frame_id', 'gateway_id'], 'required'],
      [['frame_id', 'gateway_id'], 'unique', 'targetAttribute' => ['frame_id', 'gateway_id']],
      [['frame_id', 'gateway_id', 'rssi', 'snr'], 'integer'],
      [['esp'], 'number'],
      [['created_at', 'updated_at'], 'safe'],
      [['frame_id'], 'exist', 'skipOnError' => true, 'targetClass' => Frame::className(), 'targetAttribute' => ['frame_id' => 'id']],
      [['gateway_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gateway::className(), 'targetAttribute' => ['gateway_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'frame_id' => 'Frame ID',
      'gateway_id' => 'Gateway ID',
      'rssi' => 'Rssi',
      'snr' => 'Snr',
      'esp' => 'Esp',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getFrame() {
    return $this->hasOne(Frame::className(), ['id' => 'frame_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getGateway() {
    return $this->hasOne(Gateway::className(), ['id' => 'gateway_id'])->where('1=1');
  }

  public function getLrrId() {
    return $this->gateway->lrr_id;
  }

  public function getDistance() {
    if ($this->frame->session->type == "static" && $this->frame->session->latitude !== null && $this->frame->session->longitude !== null) {
      $frameLat = $this->frame->session->latitude;
      $frameLon = $this->frame->session->longitude;
    } else {
      $frameLat = $this->frame->latitude;
      $frameLon = $this->frame->longitude;
    }
    if ($frameLat === null) {
      $distance = '';
    } elseif ($this->gateway->latitude == 0) {
      $distance = '?';
    } else {
      $distance = Calc::coordinateDistance($frameLat, $frameLon, $this->gateway->latitude, $this->gateway->longitude);
      $distance = Yii::$app->formatter->asDecimal($distance, 0);
    }
    return $distance;
  }

}
