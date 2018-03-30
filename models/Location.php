<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "locations".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $latitude
 * @property string $longitude
 * @property string $coordinates
 * @property integer $nrSessions
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Session[] $sessions
 */
class Location extends ActiveRecord {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'locations';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['name', 'latitude', 'longitude'], 'required'],
      [['description'], 'string'],
      [['created_at', 'updated_at'], 'safe'],
      [['name'], 'string', 'max' => 255],
      [['latitude', 'longitude'], 'string', 'max' => 15],
    ];
  }

  public function afterSave($insert, $changedAttributes) {
    if (!$insert && (isset($changedAttributes['latitude']) || isset($changedAttributes['longitude']))) {
      Session::updateAll(['latitude' => $this->latitude, 'longitude' => $this->longitude], 'location_id = :location_id', ['location_id' => $this->id]);
    }
    return parent::afterSave($insert, $changedAttributes);
  }

  public function getCoordinates() {
    return ($this->latitude . ',' . $this->longitude);
  }

  public function getNrSessions() {
    return $this->getSessions()->count();
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'description' => 'Description',
      'latitude' => 'Latitude',
      'longitude' => 'Longitude',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSessions() {
    return $this->hasMany(Session::className(), ['location_id' => 'id']);
  }

}
