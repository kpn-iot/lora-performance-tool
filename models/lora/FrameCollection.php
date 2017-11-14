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

/**
 * @property \app\models\Frame[] $frames
 * @property integer $nrDevices
 * @property integer $nrFrames
 * @property CoverageStats $coverage
 * @property GeolocStats $geoloc
 * @property \app\models\Frame[][] $framesPerDevice
 * @property MapData $mapData
 */
class FrameCollection extends \yii\base\BaseObject {

  private $_frames;
  private $_framesPerDevice = null, $_nrDevices = null, $_nrFrames = null, $_coverage = null, $_geoloc, $_mapData = null;

  public function __construct($frames, $config = []) {

    usort($frames, function($a, $b) {
      return $a['timestamp'] - $b['timestamp'];
    });

    $this->_frames = $frames;
    parent::__construct($config);
  }

  public function getFrames() {
    return $this->_frames;
  }

  public function getCoverage() {
    if ($this->_coverage === null) {
      $this->_coverage = new CoverageStats($this);
    }
    return $this->_coverage;
  }

  public function getGeoloc() {
    if ($this->_geoloc === null) {
      $this->_geoloc = new GeolocStats($this);
    }
    return $this->_geoloc;
  }

  public function getNrDevices() {
    if ($this->_nrDevices === null) {
      $devEUIs = [];
      foreach ($this->_frames as $frame) {
        $devEUI = $frame['device_eui'];
        if (!in_array($devEUI, $devEUIs)) {
          $devEUIs[] = $devEUI;
        }
      }
      $this->_nrDevices = count($devEUIs);
    }
    return $this->_nrDevices;
  }

  public function getNrFrames() {
    if ($this->_nrFrames === null) {
      $this->_nrFrames = count($this->_frames);
    }
    return $this->_nrFrames;
  }

  public function getFramesPerDevice() {
    if ($this->_framesPerDevice === null) {
      $this->_framesPerDevice = [];
      foreach ($this->_frames as $frame) {
        $devEUI = $frame['device_eui'];
        if (!isset($this->_framesPerDevice[$devEUI])) {
          $this->_framesPerDevice[$devEUI] = [];
        }
        $this->_framesPerDevice[$devEUI][] = $frame;
      }
    }
    return $this->_framesPerDevice;
  }

  public function getMapData() {
    if ($this->_mapData === null) {
      $this->_mapData = new MapData($this);
    }
    return $this->_mapData;
  }

}
