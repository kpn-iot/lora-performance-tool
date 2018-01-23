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

use app\components\data\Decoding;
use app\helpers\Html;

/**
 * This is the model class for table "devices".
 *
 * @property integer $id
 * @property string $name
 * @property string $device_eui
 * @property integer $port_id
 * @property string $payload_type
 * @property string $as_id
 * @property string $lrc_as_key
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property string $hash
 * @property bool $autosplit
 * @property string $autosplitFormatted
 *
 * @property Session[] $sessions
 */
class Device extends ActiveRecord {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'devices';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['name', 'device_eui', 'port_id'], 'required'],
      [['port_id'], 'integer'],
      [['description'], 'string'],
      [['created_at', 'updated_at'], 'safe'],
      [['name', 'as_id'], 'string', 'max' => 100],
      [['device_eui'], 'string', 'max' => 16],
      [['payload_type'], 'string', 'max' => 30],
      [['lrc_as_key'], 'string', 'max' => 32],
      [['autosplit'], 'boolean'],
      [['device_eui', 'port_id'], 'unique', 'targetAttribute' => ['device_eui', 'port_id'], 'message' => 'The combination of Device Eui and Port ID has already been taken.'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'device_eui' => 'DevEUI',
      'port_id' => 'Application port',
      'payload_type' => 'Payload Type',
      'payloadTypeReadable' => 'Payload type',
      'as_id' => 'AS-ID',
      'lrc_as_key' => 'LRC-AS Key',
      'description' => 'Description',
      'autosplitFormatted' => 'Autosplit',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  public function getTotalSessions() {
    return count($this->sessions);
  }

  public function getLastFrame() {
    return $this->hasOne(Frame::className(), ['session_id' => 'id'])->orderBy(['created_at' => SORT_DESC])->via('lastSession');
  }

  public function getHash() {
    return substr(md5($this->id), 1, 10);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLastSession() {
    return $this->hasOne(Session::className(), ['device_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSessions() {
    return $this->hasMany(Session::className(), ['device_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
  }

  public function getPayloadTypeReadable() {
    $supportedPayloadTypes = Decoding::getSupportedPayloadTypes();
    if ($this->payload_type == null) {
      return '<i class="not-set">n.a.</i>';
    } elseif (isset($supportedPayloadTypes[$this->payload_type])) {
      return $supportedPayloadTypes[$this->payload_type];
    }
    return null;
  }

  public function getAutosplitFormatted() {
    return ($this->autosplit) ? Html::icon('ok', ['class' => 'text-success']) : Html::icon('remove', ['class' => 'text-danger']);
  }

}
