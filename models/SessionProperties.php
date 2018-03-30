<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "session_properties".
 *
 * @property string $session_id
 * @property string $frame_counter_first
 * @property string $frame_counter_last
 * @property string $session_date_at
 * @property string $first_frame_at
 * @property string $last_frame_at
 * @property string $nr_frames
 * @property string $frame_reception_ratio
 * @property string $gateway_count_average
 * @property string $rssi_average
 * @property string $snr_average
 * @property string $esp_average
 * @property string $interval
 * @property string $runtime
 * @property string $sf_min
 * @property string $sf_max
 * @property string $geoloc_accuracy_median
 * @property string $geoloc_accuracy_average
 * @property string $geoloc_accuracy_90perc
 * @property string $geoloc_success_rate
 * @property integer $geoloc_accuracy_2d_distance
 * @property integer $geoloc_accuracy_2d_direction
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Session $session
 */
class SessionProperties extends ActiveRecord {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'session_properties';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['session_id'], 'required'],
      [['session_id'], 'unique'],
      [['session_id', 'frame_counter_first', 'frame_counter_last', 'nr_frames', 'interval', 'runtime', 'sf_min', 'sf_max'], 'integer'],
      [['session_date_at', 'first_frame_at', 'last_frame_at', 'created_at', 'updated_at'], 'safe'],
      [['frame_reception_ratio', 'gateway_count_average', 'rssi_average', 'snr_average', 'esp_average', 'geoloc_accuracy_median', 'geoloc_accuracy_average', 'geoloc_accuracy_90perc', 'geoloc_success_rate'], 'number'],
      [['session_id'], 'exist', 'skipOnError' => true, 'targetClass' => Session::className(), 'targetAttribute' => ['session_id' => 'id']],
    ];
  }

  public function beforeValidate() {
    $attrs = ['geoloc_accuracy_median', 'geoloc_accuracy_average', 'geoloc_accuracy_90perc'];
    foreach ($attrs as $attr) {
      if ($this->$attr > 20000) {
        $this->$attr = null;
      }
    }
    if ($this->interval < 0) {
      $this->interval = null;
    }
    if ($this->runtime < 0) {
      $this->runtime = null;
    }
    return true;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'session_id' => 'Session ID',
      'frame_counter_first' => 'Frame Counter First',
      'frame_counter_last' => 'Frame Counter Last',
      'session_date_at' => 'Session Date At',
      'first_frame_at' => 'First Frame At',
      'last_frame_at' => 'Last Frame At',
      'nr_frames' => 'Nr Frames',
      'frame_reception_ratio' => 'Frame Reception Ratio',
      'gateway_count_average' => 'Gateway Count Average',
      'rssi_average' => 'Rssi Average',
      'snr_average' => 'Snr Average',
      'esp_average' => 'Esp Average',
      'interval' => 'Interval',
      'runtime' => 'Runtime',
      'sf_min' => 'Sf Min',
      'sf_max' => 'Sf Max',
      'geoloc_accuracy_median' => 'Geoloc Accuracy Median',
      'geoloc_accuracy_average' => 'Geoloc Accuracy Average',
      'geoloc_accuracy_90perc' => 'Geoloc Accuracy 90perc',
      'geoloc_success_rate' => 'Geoloc Success Rate',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSession() {
    return $this->hasOne(Session::className(), ['id' => 'session_id']);
  }

}
