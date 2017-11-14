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
 * This is the model class for table "gateway_mutations".
 *
 * @property string $id
 * @property integer $gateway_id
 * @property string $old_latitude
 * @property string $old_longitude
 * @property string $old_type
 * @property string $old_state
 * @property string $new_latitude
 * @property string $new_longitude
 * @property string $new_type
 * @property string $new_state
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Gateway $gateway
 */
class GatewayMutation extends ActiveRecord {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'gateway_mutations';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['gateway_id'], 'integer'],
      [['created_at', 'updated_at'], 'safe'],
      [['old_latitude', 'old_longitude', 'old_type', 'new_latitude', 'new_longitude', 'new_type'], 'string', 'max' => 15],
      [['old_state', 'new_state'], 'string', 'max' => 30]
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'gateway_id' => 'Gateway ID',
      'old_latitude' => 'Old Latitude',
      'old_longitude' => 'Old Longitude',
      'old_type' => 'Old Type',
      'old_state' => 'Old State',
      'new_latitude' => 'New Latitude',
      'new_longitude' => 'New Longitude',
      'new_type' => 'New Type',
      'new_state' => 'New State',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getGateway() {
    return $this->hasOne(Gateway::className(), ['id' => 'gateway_id']);
  }

}
