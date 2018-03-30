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

/**
 * This is the model class for table "gateways".
 *
 * @property integer $id
 * @property string $lrr_id
 * @property string $type
 * @property string $latitude
 * @property string $longitude
 * @property string $created_at
 * @property string $updated_at
 */
class Gateway extends ActiveRecord {

  static $typeOptions = ['omni' => 'Omni', 'sectorized' => 'Sectorized'];

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'gateways';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['lrr_id'], 'string', 'max' => 8],
      [['latitude', 'longitude'], 'string', 'max' => 15],
      [['type'], 'in', 'range' => array_keys(static::$typeOptions)]
    ];
  }

  public static function find() {
    return parent::find()->andWhere('deleted_at IS NULL');
  }

  public function delete() {
    $this->deleted_at = gmdate('Y-m-d H:i:s');
    if (!$this->save()) {
      return false;
    }
    $log = new GatewayMutation();
    $log->gateway_id = $this->id;
    $log->old_state = 'active';
    $log->new_state = 'deleted';
    $log->save();
    return true;
  }

  public function afterSave($insert, $changedAttributes) {
    if ($insert) {
      return;
    }

    $monitor = ['latitude', 'longitude', 'type'];
    $action = false;
    $log = new GatewayMutation();
    foreach ($monitor as $attr) {
      if (isset($changedAttributes[$attr])) {
        $action = true;

        if ($attr === 'deleted_at') {
          $log->old_state = ($changedAttributes['deleted_at'] === null) ? 'active' : 'deleted';
          $log->new_state = ($this->deleted_at === null) ? 'active' : 'deleted';
        } else {
          $oldAttr = "old_" . $attr;
          $newAttr = "new_" . $attr;
          $log->$oldAttr = $changedAttributes[$attr];
          $log->$newAttr = $this->$attr;
        }
      }
    }
    if (!$action) {
      return;
    }

    $log->gateway_id = $this->id;
    if (!$log->save()) {
      throw new \yii\web\HttpException(400, json_encode($log->errors));
    }

    parent::afterSave($insert, $changedAttributes);
  }

  public function getCoordinates() {
    if ($this->deleted_at !== null) {
      return 'Removed';
    }
    return $this->latitude . ', ' . $this->longitude;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'lrr_id' => 'LRR ID',
      'type' => 'Type',
      'latitude' => 'Latitude',
      'longitude' => 'Longitude',
      'created_at' => 'First seen',
      'updated_at' => 'Last seen',
    ];
  }

  public function getReception() {
    return $this->hasMany(Reception::className(), ['gateway_id' => 'id'])->with('frame');
  }

}
