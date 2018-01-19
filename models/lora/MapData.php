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

namespace app\models\lora;

use app\helpers\Calc;

/**
 * @property array $data
 */
class MapData extends \yii\base\BaseObject {

  private $_frameCollection, $_mapData = null;

  public function __construct(FrameCollection $frameCollection, $config = []) {
    $this->_frameCollection = $frameCollection;
    parent::__construct($config);
  }

  public function getData() {
    if ($this->_mapData === null) {
      $mapData = static::parseFrames($this->_frameCollection->frames);
      $this->_mapData = static::interpolateMissingFrames($mapData, true);
    }
    return $this->_mapData;
  }

  public static function parseFrames($framesIn) {
    $mapData = [];

    foreach ($framesIn as $frame) {
      $mapDataItem = [
        'created_at' => $frame->created_at,
        'f_cnt_up' => $frame->count_up,
        'channel' => $frame->channel,
        'sf' => $frame->sf,
        'gateway_count' => $frame->gateway_count,
        'latitude_lora' => $frame->latitude_lora,
        'longitude_lora' => $frame->longitude_lora,
        'location_age_lora' => $frame->location_age_lora,
        'raw_timestamp' => $frame->time
      ];

      $mapDataItem['timestamp'] = strtotime($mapDataItem['created_at']);
      $mapDataItem['time'] = date('Y-m-d H:i:s', $mapDataItem['timestamp']);

      if ($frame->latitude == null || $frame->longitude == "0") {
        $mapDataItem['latitude'] = null;
        $mapDataItem['longitude'] = null;
      } else {
        $mapDataItem['latitude'] = $frame->latitude;
        $mapDataItem['longitude'] = $frame->longitude;
      }

      if (isset($mapDataItem['latitude_lora']) && $mapDataItem['latitude'] != 0) {
        $mapDataItem['location_diff_lora'] = Calc::coordinateDistance((float) $mapDataItem['latitude'], (float) $mapDataItem['longitude'], (float) $mapDataItem['latitude_lora'], (float) $mapDataItem['longitude_lora']);
      }

      $mapDataItem['reception'] = [];
      if (isset($frame['reception'])) {
        foreach ($frame['reception'] as $reception) {
          $mapDataItem['reception'][] = $reception->gateway->lrr_id;
        }
      }

      $mapData[] = $mapDataItem;
    }

    return $mapData;
  }

  /*
   * This function also removes duplicate records...
   */

  public static function interpolateMissingFrames($mapData, $interpolateLocation = true) {
    $enrichedMapData = [];
    $previousFrameCounter = null;
    $previousItem = null;
    foreach ($mapData as $item) {
      $previousFrameCounter = $previousItem['f_cnt_up'];
      $frameCounter = $item['f_cnt_up'];
      if ($previousFrameCounter != null && $previousFrameCounter < $frameCounter - 1) {
        $nrFramesMissing = $frameCounter - $previousFrameCounter - 1;

        // Always pick the smallest value for the spreading factor of a missing frame
        if ($previousItem['sf'] < $item['sf']) {
          $spFact = $previousItem['sf'];
        } else {
          $spFact = $item['sf'];
        }

        $latitudeDiff = $item['latitude'] - $previousItem['latitude'];
        $longitudeDiff = $item['longitude'] - $previousItem['longitude'];

        $missingI = 0;
        do {
          if ($interpolateLocation && $item['latitude'] != null && $item['longitude'] != null && $previousItem['latitude'] != null && $previousItem['longitude'] != null) {
            $newLatitude = $previousItem['latitude'] + ((($missingI + 1) / ($nrFramesMissing + 1)) * $latitudeDiff);
            $newLongitude = $previousItem['longitude'] + ((($missingI + 1) / ($nrFramesMissing + 1)) * $longitudeDiff);
          } else {
            $newLatitude = null;
            $newLongitude = null;
          }
          $missingI++;
          $previousFrameCounter++;
          $enrichedMapData[] = [
            'f_cnt_up' => $previousFrameCounter,
            'created_at' => '',
            'timestamp' => '',
            'time' => '',
            'latitude' => $newLatitude,
            'longitude' => $newLongitude,
            'sf' => $spFact,
            'isMissed' => true
          ];
        } while ($previousFrameCounter < $frameCounter - 1);
      } elseif ($previousFrameCounter > $frameCounter - 1) {
        // If there is a double record, skip it!
        continue;
      }
      $previousItem = $item;
      $enrichedMapData[] = $item;
    }
    return $enrichedMapData;
  }

}
