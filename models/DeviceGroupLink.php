<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "device_group_links".
 *
 * @property int $group_id
 * @property int $device_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Device $device
 * @property DeviceGroup $group
 */
class DeviceGroupLink extends ActiveRecord {
  /**
   * {@inheritdoc}
   */
  public static function tableName() {
    return 'device_group_links';
  }

  /**
   * {@inheritdoc}
   */
  public function rules() {
    return [
        [['group_id', 'device_id'], 'required'],
        [['group_id', 'device_id'], 'integer'],
        [['created_at', 'updated_at'], 'safe'],
        [['group_id', 'device_id'], 'unique', 'targetAttribute' => ['group_id', 'device_id']],
        [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::className(), 'targetAttribute' => ['device_id' => 'id']],
        [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => DeviceGroup::className(), 'targetAttribute' => ['group_id' => 'id']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels() {
    return [
        'group_id' => 'Group ID',
        'device_id' => 'Device ID',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ];
  }

  /**
   * Gets query for [[Device]].
   *
   * @return \yii\db\ActiveQuery
   */
  public function getDevice() {
    return $this->hasOne(Device::className(), ['id' => 'device_id']);
  }

  /**
   * Gets query for [[Group]].
   *
   * @return \yii\db\ActiveQuery
   */
  public function getGroup() {
    return $this->hasOne(DeviceGroup::className(), ['id' => 'group_id']);
  }
}
