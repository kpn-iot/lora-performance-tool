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
use yii\web\UploadedFile;
use app\helpers\Calc;
use app\helpers\Html;
use yii\web\HttpException;
use app\components\data\Decoding;

/**
 * This is the model class for table "quicks".
 *
 * @property integer $id
 * @property string $name
 * @property string $latitude
 * @property string $longitude
 * @property string $created_at
 * @property string $updated_at
 * @property string $filePath
 * @property string $type
 * @property string $payload_type
 * 
 * @property lora\FrameCollection $frameCollection
 */
class Quick extends ActiveRecord {

  public $file;
  private $_frameCollection = null;
  static $typeOptions = ['moving' => 'Moving measurement', 'static' => 'Static measurement'];

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'quicks';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['file'], 'file', 'extensions' => 'csv', 'checkExtensionByMimeType' => false],
      [['name', 'type'], 'required'],
      ['file', 'required', 'on' => 'create'],
      [['name'], 'string', 'max' => 100],
      [['latitude', 'longitude'], 'string', 'max' => 20],
      ['type', 'in', 'range' => array_keys(static::$typeOptions)],
      ['payload_type', 'in', 'range' => array_keys(\app\components\data\Decoding::getSupportedPayloadTypes())],
    ];
  }

  public function beforeValidate() {
    if (($file = UploadedFile::getInstance($this, 'file')) != null) {
      $this->file = $file;
    }
    return parent::beforeValidate();
  }

  public function afterSave($insert, $changedAttributes) {
    if ($this->file instanceof UploadedFile) {
      $this->file->saveAs($this->filePath);
    }
  }

  public function afterDelete() {
    @unlink($this->filePath);
  }

  public static function getFileDir() {
    return Yii::getAlias('@app/files/quicks');
  }

  public function getFilePath() {
    return static::getFileDir() . '/' . $this->id . '.csv';
  }

  public function getFrameCollection() {
    if ($this->_frameCollection === null) {
      $frames = $this->getFramesFromCsv();
      $this->_frameCollection = new lora\FrameCollection($frames);
    }
    return $this->_frameCollection;
  }

  public function getNrFrames() {
    return $this->frameCollection->nrFrames;
  }

  public function getCoordinates() {
    return $this->latitude . ', ' . $this->longitude;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'latitude' => 'Latitude',
      'longitude' => 'Longitude',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
      'typeFormatted' => 'Type'
    ];
  }

  private function getFramesFromCsv() {
    $frames = [];
    $loraLocationTemp = [];
    if (($handle = @fopen($this->filePath, "r")) === false) {
      throw new HttpException(400, 'A CSV-file should be uploaded');
    }

    $gateways = Gateway::find()->all();
    $gatewayLocations = [];
    foreach ($gateways as $gateway) {
      $gatewayLocations[$gateway->lrr_id] = [
        'latitude' => $gateway->latitude,
        'longitude' => $gateway->longitude
      ];
    }

    while (($data = fgetcsv($handle, 0, ',')) !== false) {
      if ($data[0] != '0' || $data[5] == 'None') {
        continue;
      }

      $latitude = null;
      $longitude = null;
      if ($this->payload_type != null) {
        $payload = $data[84];
        $info = Decoding::decode($payload, $this->payload_type);
        if (isset($info['latitude'])) {
          $latitude = $info['latitude'];
        }
        if (isset($info['longitude'])) {
          $longitude = $info['longitude'];
        }
      } elseif ($this->type == 'static' && $this->latitude != null && $this->longitude != null) {
        $latitude = $this->latitude;
        $longitude = $this->longitude;
      }

      $frame = [
        'device_eui' => $data[3],
        'count_up' => (int) $data[6],
        'sf' => (int) $data[10],
        'channel' => $data[12],
        'gateway_count' => (int) $data[17],
        'latitude' => $latitude,
        'longitude' => $longitude,
        'latitude_lora' => null,
        'longitude_lora' => null,
        'location_time_lora' => null,
        'location_age_lora' => null,
        'distance' => null,
        'time' => $data[2],
        'created_at' => $data[2],
        'timestamp' => strtotime($data[2])
      ];

      $frame['reception'] = [];
      $pointer = 26;
      while ($data[$pointer] != '' && $pointer < 76) {
        $lrrId = $data[$pointer];
        if (isset($gatewayLocations[$lrrId])) {
          $gwInfo = $gatewayLocations[$lrrId];
          if ($gwInfo['latitude'] == 0) {
            $distance = "?";
          } else {
            $distance = Calc::coordinateDistance($latitude, $longitude, $gwInfo['latitude'], $gwInfo['longitude']);
            $distance = Yii::$app->formatter->asDecimal($distance, 0) . 'm';
          }
        } else {
          $distance = null;
        }
        $frame['reception'][] = [
          'lrrId' => $data[$pointer],
          'distance' => $distance,
          'rssi' => (float) $data[$pointer + 1],
          'snr' => (float) $data[$pointer + 2],
          'esp' => (float) $data[$pointer + 3],
          'chain' => $data[$pointer + 4]
        ];
        $pointer += 5;
      }

      if (isset($loraLocationTemp[$frame['device_eui']])) {
        $frame = array_merge($frame, $loraLocationTemp[$frame['device_eui']]);
        $frame['location_age_lora'] = strtotime($frame['created_at']) - strtotime($frame['location_time_lora']);

        if ($frame['latitude'] != null && $frame['longitude'] != null && $frame['latitude_lora'] != null && $frame['longitude_lora'] != null) {
          $frame['distance'] = Calc::coordinateDistance($frame['latitude'], $frame['longitude'], $frame['latitude_lora'], $frame['longitude_lora']);
        }
      }

      $loraLocationTemp[$frame['device_eui']] = [
        'latitude_lora' => $data[18],
        'longitude_lora' => $data[19],
        'location_time_lora' => $data[22]
      ];

      $frame['coordinates'] = Yii::$app->formatter->asCoordinates($frame['latitude'] . ', ' . $frame['longitude']);
      $frame['loraCoordinates'] = Yii::$app->formatter->asCoordinates($frame['latitude_lora'] . ', ' . $frame['longitude_lora']);

      $frames[] = $frame;
    }
    fclose($handle);

    return array_reverse($frames);
  }

  public function getTypeIcon() {
    switch ($this->type) {
      case 'moving':
        return Html::icon('globe');
      case 'static':
        return Html::icon('pushpin');
    }
  }

  public function getTypeFormatted() {
    $str = $this->typeIcon . ' ';
    switch ($this->type) {
      case 'moving':
        return $str . "Moving";
      case 'static':
        $str .= "Static";
        if ($this->latitude != null && $this->longitude != null) {
          $str .= " @ " . Yii::$app->formatter->asCoordinates($this->latitude . ',' . $this->longitude);
        }
        return $str;
    }
  }

}
