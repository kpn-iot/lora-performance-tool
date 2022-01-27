<?php

namespace app\models;

use app\helpers\DataHelper;
use app\models\forms\DeviceGroupGraphForm;
use Yii;

/**
 * This is the model class for table "device_groups".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property DeviceGroupLink[] $deviceGroupLinks
 * @property Device[] $devices
 */
class DeviceGroup extends ActiveRecord {
  /**
   * {@inheritdoc}
   */
  public static function tableName() {
    return 'device_groups';
  }

  /**
   * {@inheritdoc}
   */
  public function rules() {
    return [
      [['name'], 'required'],
      [['description'], 'string'],
      [['created_at', 'updated_at'], 'safe'],
      [['name'], 'string', 'max' => 255],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'description' => 'Description',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * Gets query for [[DeviceGroupLinks]].
   *
   * @return \yii\db\ActiveQuery
   */
  public function getDeviceGroupLinks() {
    return $this->hasMany(DeviceGroupLink::className(), ['group_id' => 'id']);
  }

  /**
   * Gets query for [[Devices]].
   *
   * @return \yii\db\ActiveQuery
   */
  public function getDevices() {
    return $this->hasMany(Device::className(), ['id' => 'device_id'])->viaTable('device_group_links', ['group_id' => 'id']);
  }

  /**
   * @param DeviceGroupGraphForm $formModel
   * @return null|array
   * @throws \yii\db\Exception
   */
  public function getAccuracyHistogramData(DeviceGroupGraphForm $formModel) {
    if (!$formModel->validate()) {
      return null;
    }

    return DataHelper::getAccuracyHistogramData('deviceGroup', $this->id, $formModel);
  }

  /**
   * @param DeviceGroupGraphForm $formModel
   * @return null|array
   * @throws \yii\db\Exception
   */
  public function getDailyStatsData(DeviceGroupGraphForm $formModel) {
    if (!$formModel->validate()) {
      return null;
    }

    return DataHelper::getDailyStatsData('deviceGroup', $this->id, $formModel);
  }
}
