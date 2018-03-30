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
 * @property string $runtime
 * @property string $countUpRange
 * @property integer $frr
 * @property integer $avgGwCount
 * @property integer $nrFrames
 * @property string $scope
 * @property string $name
 * @property string $fullName
 * @property string $locSolveAccuracy
 * @property string $locSolveSuccess
 * @property integer $interval
 * @property integer $sf
 * @property string $typeIcon
 * @property string $vehicleTypeIcon
 * @property string $vehicleTypeFormatted
 * @property string $motionIndicatorReadable
 * @property string $typeFormatted
 *
 * @property SessionProperties $prop
 * @property FrameCollection $frameCollection
 * @property Frame $firstFrame
 * @property Frame $lastFrame
 * @property Frame[] $frames
 * @property Device $device
 * @property Location $location
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
      [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => Location::className(), 'targetAttribute' => ['location_id' => 'id']],
    ];
  }

  // for soft delete
  public static function find() {
    return parent::find()->andWhere('deleted_at IS NULL');
  }

  // for soft delete
  public function delete() {
    $this->deleted_at = gmdate('Y-m-d H:i:s');
    return $this->save();
  }

  public function beforeSave($insert) {
    $emptyOk = ['description', 'latitude', 'longitude', 'location_id'];
    foreach ($emptyOk as $attribute) {
      if ($this->$attribute === '') {
        $this->$attribute = null;
      }
    }

    if ($this->location_id !== null) {
      $this->latitude = $this->location->latitude;
      $this->longitude = $this->location->longitude;
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
      'nrFrames' => 'Nr received frames',
      'frr' => 'FRR',
      'typeIcon' => 'Type',
      'typeFull' => 'Measurement type',
      'typeFormatted' => 'Type',
      'location_id' => 'Location',
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

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLocation() {
    return $this->hasOne(Location::className(), ['id' => 'location_id']);
  }

  /**
   * 
   * @return \yii\db\ActiveQuery
   */
  public function getProperties() {
    return $this->hasOne(SessionProperties::className(), ['session_id' => 'id']);
  }

  /* PROPERTIES */

  private $_prop = null;

  public function getProp() {
    if ($this->_prop === null) {
      $properties = $this->properties;
      if ($properties === null) {
        $properties = $this->updateProperties(true);
      }
      $this->_prop = $properties;
    }
    return $this->_prop;
  }

  public function getCountUpRange() {
    $str = $this->prop->frame_counter_first;
    if ($this->prop->frame_counter_last !== $this->prop->frame_counter_first) {
      $str .= ' - ' . $this->prop->frame_counter_last;
    }
    return $str;
  }

  public function getInterval() {
    return FrameCollection::formatInterval($this->prop->interval);
  }
  
  public function getAvgGwCount() {
    return $this->prop->gateway_count_average;
  }

  public function getSf() {
    if ($this->prop->sf_max === $this->prop->sf_min) {
      return "SF" . $this->prop->sf_max;
    }
    return "SF" . $this->prop->sf_min . '-' . $this->prop->sf_max;
  }

  public function getLocSolveSuccess() {
    if ($this->prop->geoloc_success_rate === null) {
      return null;
    }
    return round($this->prop->geoloc_success_rate) . "%";
  }

  public function getFrr() {
    if ($this->prop->frame_reception_ratio === null) {
      return null;
    }
    return round($this->prop->frame_reception_ratio, 1) . "%";
  }

  public function getRuntime() {
    if ($this->prop->runtime === 0) {
      return null;
    }

    $sec = $this->prop->runtime;
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

  public function getNrFrames() {
    return $this->prop->nr_frames;
  }

  public function getScope() {
    return $this->prop->frame_counter_last - $this->prop->frame_counter_first + 1;
  }

  public function getLocSolveAccuracy() {
    if ($this->prop->geoloc_accuracy_average === null) {
      return null;
    }
    return Yii::$app->formatter->asDistance($this->prop->geoloc_accuracy_average);
  }

  /** OTHERS * */
  public function getName() {
    return ($this->description != null) ? $this->description : 'Session ' . $this->id;
  }

  public function getFullName() {
    return '[' . $this->id . '] ' . $this->name . ' (' . $this->device->name . ')';
  }

  public function getFrameCollection() {
    if ($this->_frameCollection === null) {
      $this->_frameCollection = new FrameCollection($this->frames);
    }
    return $this->_frameCollection;
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
        if ($this->location_id !== null) {
          $str .= " @ " . Html::a($this->location->name, ['/locations/view', 'id' => $this->location_id]);
        } elseif ($this->latitude !== null && $this->longitude !== null) {
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

  public function updateProperties($createOnly = false, $refreshModel = true) {
    if ($createOnly && $this->properties !== null) {
      return $this->properties;
    }
    
    if ($refreshModel) {
      $session = Session::find()->with(['firstFrame', 'lastFrame', 'frames', 'properties', 'frames.session', 'frames.reception'])->andWhere(['id' => $this->id])->one();
    } else {
      $session = $this;
    }
    
    if ($session->properties === null) {
      $properties = new SessionProperties();
      $properties->session_id = $this->id;
    } else {
      $properties = $session->properties;
    }

    if ($session->firstFrame === null) {
      $session->delete();
      return null;
    }
    $properties->frame_counter_first = $session->firstFrame->count_up;
    $properties->frame_counter_last = $session->lastFrame->count_up;
    $properties->session_date_at = gmdate("Y-m-d H:i:s", (($session->lastFrame->timestamp - $session->firstFrame->timestamp) / 2) + $session->firstFrame->timestamp);
    $properties->first_frame_at = gmdate("Y-m-d H:i:s", $session->firstFrame->timestamp);
    $properties->last_frame_at = gmdate("Y-m-d H:i:s", $session->lastFrame->timestamp);
    $properties->nr_frames = $session->getFrames()->count();
    $properties->frame_reception_ratio = round(100 * $properties->nr_frames / ($session->lastFrame->count_up - $session->firstFrame->count_up + 1), 2);
    $properties->gateway_count_average = $session->frameCollection->coverage->avgGwCount;
    $properties->rssi_average = $session->frameCollection->coverage->avgRssi;
    $properties->snr_average = $session->frameCollection->coverage->avgSnr;
    $properties->esp_average = $session->frameCollection->coverage->avgEsp;
    $properties->interval = $session->frameCollection->getInterval(false);
    $properties->runtime = ($session->lastFrame->timestamp - $session->firstFrame->timestamp);
    $properties->sf_max = $session->frameCollection->sfMax;
    $properties->sf_min = $session->frameCollection->sfMin;
    $properties->geoloc_accuracy_median = $session->frameCollection->geoloc->median;
    $properties->geoloc_accuracy_average = $session->frameCollection->geoloc->average;
    $properties->geoloc_accuracy_90perc = $session->frameCollection->geoloc->perc90point;
    $properties->geoloc_accuracy_2d_distance = $session->frameCollection->geoloc->average2D['distance'];
    $properties->geoloc_accuracy_2d_direction = $session->frameCollection->geoloc->average2D['direction'];
    $properties->geoloc_success_rate = $session->frameCollection->geoloc->percentageNrLocalisations * 100;
    if (!$properties->save()) {
      throw new \yii\web\HttpException(400, json_encode($properties->errors));
    }
    $session = null;
    return $properties;
  }

}
