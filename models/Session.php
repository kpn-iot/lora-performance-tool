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
use app\helpers\Html;
use app\models\lora\FrameCollection;

/**
 * This is the model class for table "sessions".
 *
 * @property string $id
 * @property integer $device_id
 * @property string $description
 * @property string $type
 * @property string $vehicle_type
 * @property string $motion_indicator
 * @property string $latitude
 * @property string $longitude
 * @property string $created_at
 * @property string $updated_at
 * @property string runtime
 * @property string countUpRange
 * @property string lastCountUp
 * @property string frr
 * @property string frrRel
 * @property string scope
 * @property string name
 * @property string fullName
 * @property string locSolveAccuracy
 * @property string locSolveSuccess
 * @property integer interval
 * @property integer $sf
 * @property string typeIcon
 * @property string vehicleTypeIcon
 * @property string vehicleTypeFormatted
 * @property string motionIndicatorReadable
 * @property string typeFormatted
 *
 * @property FrameCollection $frameCollection
 * @property Frame|null $lastFrame
 * @property Frame[] $frames
 * @property Device $device
 */
class Session extends ActiveRecord {

  private $_frameCollection = null;
  static $typeOptions = ['moving' => 'Moving measurement', 'static' => 'Static measurement'];
  static $vehicleTypeOptions = ['unknown' => 'Unknown', 'no' => 'No vehicle', 'walking' => 'Walking', 'car' => 'Car', 'bike' => 'Bike', 'train' => 'Train', 'plane' => 'Plane', 'tank' => 'Tank', 'drone' => 'Drone'];
  static $motionIndicatorOptions = ['unknown' => 'Unkown', 'static' => 'Static', 'slow_moving' => 'Walking speed', 'fast_moving' => 'Vehicle speed', 'nomadic' => 'Random'];

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'sessions';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['device_id'], 'integer'],
      [['description', 'latitude', 'longitude'], 'string'],
      ['type', 'in', 'range' => array_keys(static::$typeOptions)],
      ['vehicle_type', 'in', 'range' => array_keys(static::$vehicleTypeOptions)],
      ['motion_indicator', 'in', 'range' => array_keys(static::$motionIndicatorOptions)],
      [['created_at', 'updated_at'], 'safe'],
      [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::className(), 'targetAttribute' => ['device_id' => 'id']],
    ];
  }

  // for soft delete
  public static function find() {
    return parent::find()->andWhere('deleted_at IS NULL');
  }

  // for soft delete
  public function delete() {
    $this->deleted_at = date('Y-m-d H:i:s');
    return $this->save();
  }

  public function beforeSave($insert) {
    $emptyOk = ['description', 'latitude', 'longitude'];
    foreach ($emptyOk as $attribute) {
      if ($this->$attribute === '') {
        $this->$attribute = null;
      }
    }

    return parent::beforeSave($insert);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'device_id' => 'Device ID',
      'frr' => 'Nr received frames',
      'frrRel' => 'Frame reception ratio',
      'typeIcon' => 'Type',
      'typeFull' => 'Measurement type',
      'vehicleTypeReadable' => 'Vehicle type',
      'motionIndicatorReadable' => 'Motion indicator',
      'countUpRange' => 'Counter range',
      'description' => 'Description',
      'sf' => 'SF',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
      'locSolveAccuracy' => 'LocSolve Accuracy',
      'locSolveSuccess' => 'LocSolve Success'
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getFrames() {
    return $this->hasMany(Frame::className(), ['session_id' => 'id'])->orderBy(['count_up' => SORT_ASC]);
  }

  public function getFrameCollection() {
    if ($this->_frameCollection === null) {
      $this->_frameCollection = new FrameCollection($this->frames);
    }
    return $this->_frameCollection;
  }

  /**
   * @return Frame|null
   */
  public function getFirstFrame() {
    return $this->hasOne(Frame::className(), ['session_id' => 'id'])->orderBy(['count_up' => SORT_ASC]);
  }

  /**
   * @return Frame|null
   */
  public function getLastFrame() {
    return $this->hasOne(Frame::className(), ['session_id' => 'id'])->orderBy(['count_up' => SORT_DESC]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getDevice() {
    return $this->hasOne(Device::className(), ['id' => 'device_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSessionSetLinks() {
    return $this->hasMany(SessionSetLink::className(), ['session_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSessionSets() {
    return $this->hasMany(SessionSet::className(), ['id' => 'set_id'])->via('sessionSetLinks');
  }

  public function getRuntime() {
    if ($this->lastFrame === null || $this->firstFrame === null) {
      return null;
    }

    $sec = strtotime($this->lastFrame->created_at) - strtotime($this->firstFrame->created_at);
    if ($sec < 60) {
      return '< 1 min';
    }
    $min = $sec / 60;
    if ($min < 60) {
      return "00:" . sprintf('%02d', $min);
    }
    $hour = floor($min / 60);
    if ($hour < 24) {
      $min = $min - ($hour * 60);
      return sprintf('%02d', $hour) . ":" . sprintf('%02d', $min);
    }
    $day = $hour / 24;
    return round($day) . (($day < 1.5) ? ' day' : ' days');
  }

  public function getCountUpRange() {
    $str = '';
    if ($this->firstFrame != null) {
      $str .= $this->firstFrame->count_up;
    }
    if ($this->lastFrame != null) {
      $str .= ' - ' . $this->lastFrame->count_up;
    }
    return $str;
  }

  public function getLastCountUp() {
    return $this->lastFrame->count_up;
  }

  public function getFrr() {
    return count($this->frames);
  }

  public function getFrrRel() {
    if ($this->scope == 0) {
      return 0;
    }
    return round(100 * $this->frr / $this->scope, 1) . '%';
  }

  public function getScope() {
    if ($this->lastFrame == null || $this->firstFrame == null) {
      return 0;
    }
    return $this->lastFrame->count_up - $this->firstFrame->count_up + 1;
  }

  public function getName() {
    return ($this->description != null) ? $this->description : 'Session ' . $this->id;
  }

  public function getFullName() {
    return '[' . $this->id . '] ' . $this->name . ' (' . $this->device->name . ')';
  }

  public function getLocSolveAccuracy() {
    if ($this->frameCollection->geoloc->nrMeasurements == 0) {
      return null;
    }
    return Yii::$app->formatter->asDistance($this->frameCollection->geoloc->average);
  }

  public function getLocSolveSuccess() {
    if ($this->frameCollection->geoloc->nrMeasurements === 0) {
      return null;
    }
    return round($this->frameCollection->geoloc->percentageNrLocalisations * 100) . "%";
  }

  public function getInterval() {
    return $this->frameCollection->interval;
  }

  public function getSf() {
    $sf = $this->frameCollection->sf;
    if ($sf === null) {
      return 'Variable';
    } else {
      return 'SF' . $sf;
    }
  }

  public function getTypeIcon() {
    switch ($this->type) {
      case 'moving':
        return Html::icon('globe');
      case 'static':
        return Html::icon('pushpin');
    }
  }

  public function getVehicleTypeIcon() {
    switch ($this->vehicle_type) {
      case 'car':
        return Html::fa('car');
      case 'bike':
        return Html::fa('bicycle');
      case 'plane':
        return Html::fa('plane');
      case 'train':
        return Html::fa('train');
      case 'no':
        return Html::fa('asterisk');
      case 'walking':
        return Html::fa('user');
      default:
        return null;
    }
  }

  public function getTypeFormatted() {
    $str = $this->typeIcon . ' ';
    switch ($this->type) {
      case 'moving':
        return $str . "Moving";
      case 'static':
        $str .= "Static";
        if ($this->latitude !== null && $this->longitude !== null) {
          $str .= " @ " . Yii::$app->formatter->asCoordinates($this->latitude . ',' . $this->longitude);
        }
        return $str;
    }
  }

  public function getVehicleTypeFormatted() {
    if ($this->vehicle_type == null || !isset(static::$vehicleTypeOptions[$this->vehicle_type])) {
      return $this->vehicle_type;
    }
    return $this->vehicleTypeIcon . ' ' . static::$vehicleTypeOptions[$this->vehicle_type];
  }

  public function getMotionIndicatorReadable() {
    if ($this->motion_indicator == null || !isset(static::$motionIndicatorOptions[$this->motion_indicator])) {
      return $this->motion_indicator;
    }
    return static::$motionIndicatorOptions[$this->motion_indicator];
  }

}
