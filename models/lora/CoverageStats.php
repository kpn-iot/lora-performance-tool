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
 * @property integer $avgRssi
 * @property integer $avgGwCount
 * @property integer $avgSnr
 * @property integer $avgEsp
 * @property array $sfUsage
 * @property array $channelUsage
 * @property array $gwCountPdf
 * @property array $gwColors
 * @property array $timeline
 * @property array $graphs
 */
class CoverageStats extends \yii\base\BaseObject {

  private $_frameCollection;
  private $_calculated = false;
  private $_avgRssi, $_avgGwCount = null, $_avgSnr, $_avgEsp, $_sfUsage, $_channelUsage, $_gwCountPdf, $_gwColors, $_timeline, $_graphs, $noFrames = null;
  public static $colorList = ["#3366CC", "#DC3912", "#FF9900", "#109618", "#990099", "#3B3EAC", "#0099C6", "#DD4477", "#66AA00", "#B82E2E", "#316395", "#994499", "#22AA99", "#AAAA11", "#6633CC", "#E67300", "#8B0707", "#329262", "#5574A6", "#3B3EAC"];

  public function __construct(FrameCollection $frameCollection, $config = []) {
    $this->_frameCollection = $frameCollection;
    $this->noFrames = ($frameCollection->nrFrames === 0);
    parent::__construct($config);
  }

  private function calculate() {
    $frames = $this->_frameCollection->frames;

    $this->_sfUsage = ['7' => 0, '8' => 0, '9' => 0, '10' => 0, '11' => 0, '12' => 0];
    $this->_channelUsage = ['LC1' => 0, 'LC2' => 0, 'LC3' => 0, 'LC4' => 0, 'LC5' => 0, 'LC6' => 0, 'LC7' => 0, 'LC8' => 0];
    $this->_gwCountPdf = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0];

    $this->_timeline = [];
    $strongestRssiList = [];
    $strongestSnrList = [];
    $strongestEspList = [];
    $gatewayIdList = [];
    $receptionPerGateway = [];

    foreach ($frames as $frame) {
      $this->_timeline[] = [
        $frame['time'],
        (int) $frame['sf'],
        (int) $frame['gateway_count'],
        ($frame['channel'] != '') ? ((int) str_replace('LC', '', $frame['channel'])) : null
      ];

      if ($frame['sf'] != '') {
        $this->_sfUsage[$frame['sf']] += 1;
      }
      $this->_gwCountPdf[$frame['gateway_count']] += 1;
      if ($frame['channel'] != '') {
        $this->_channelUsage[$frame['channel']] += 1;
      }

      if (count($frame['reception']) > 0) {
        $strongestRssiList[] = $frame['reception'][0]['rssi'];
        $strongestSnrList[] = $frame['reception'][0]['snr'];
        $strongestEspList[] = $frame['reception'][0]['esp'];
      }

      foreach ($frame['reception'] as $reception) {
        $gatewayId = $reception['lrrId'];
        if (!in_array($gatewayId, $gatewayIdList)) {
          $gatewayIdList[] = $gatewayId;
        }

        if (!isset($receptionPerGateway[$gatewayId])) {
          $receptionPerGateway[$gatewayId] = [];
        }
        $receptionPerGateway[$gatewayId][] = [
          'lrrId' => $reception['lrrId'],
          'distance' => $reception['distance'],
          'rssi' => $reception['rssi'],
          'snr' => $reception['snr'],
          'esp' => $reception['esp'],
          'time' => $frame['time']
        ];
      }
    }

    $this->_graphs = [
      'columns' => [],
      'lines' => [
        'rssi' => [],
        'snr' => []
      ]
    ];
    $colSkip = 0;
    foreach ($receptionPerGateway as $gatewayId => $receptionSet) {
      $this->_graphs['columns'][] = $gatewayId . ' (' . count($receptionSet) . ')';
      foreach ($receptionSet as $reception) {
        $newLineRssi = [$reception['time']];
        $newLineSnr = [$reception['time']];
        for ($i = 0; $i < $colSkip; $i++) {
          $newLineRssi[] = null;
          $newLineSnr[] = null;
        }
        $newLineRssi[] = $reception['rssi'];
        $newLineSnr[] = $reception['snr'];
        for ($i = $colSkip + 1; $i < count($receptionPerGateway); $i++) {
          $newLineRssi[] = null;
          $newLineSnr[] = null;
        }
        $this->_graphs['lines']['rssi'][] = $newLineRssi;
        $this->_graphs['lines']['snr'][] = $newLineSnr;
      }
      $colSkip += 1;
    }

    $this->_gwColors = [];
    foreach ($gatewayIdList as $index => $gateway) {
      if ($index < count(static::$colorList)) {
        $this->_gwColors[$gateway] = static::$colorList[$index];
      } else {
        $this->_gwColors[$gateway] = "#666666";
      }
    }

    foreach ($this->_sfUsage as &$sf) {
      $sf = round(100 * $sf / count($frames), 1);
    }

    $this->_avgRssi = (count($strongestRssiList) === 0) ? null : round(array_sum($strongestRssiList) / count($strongestRssiList), 2);
    $this->_avgSnr = (count($strongestSnrList) === 0) ? null : round(array_sum($strongestSnrList) / count($strongestSnrList), 2);
    $this->_avgEsp = (count($strongestEspList) === 0) ? null : round(array_sum($strongestEspList) / count($strongestEspList), 2);

    $this->_calculated = true;
  }

  public function getSfUsage() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_sfUsage;
  }

  public function getChannelUsage() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_channelUsage;
  }

  public function getGwCountPdf() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_gwCountPdf;
  }

  public function getGwColors() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_gwColors;
  }

  public function getTimeline() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_timeline;
  }

  public function getGraphs() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_graphs;
  }

  public function getAvgRssi() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_avgRssi;
  }

  public function getAvgGwCount() {
    if ($this->_avgGwCount === null) {
      if ($this->noFrames) {
        return $this->_avgGwCount;
      }
      $gwCountList = [];
      foreach ($this->_frameCollection->frames as $frame) {
        $gwCountList[] = $frame['gateway_count'];
      }
      $this->_avgGwCount = round(array_sum($gwCountList) / count($gwCountList), 2);
    }
    return $this->_avgGwCount;
  }

  public function getAvgSnr() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_avgSnr;
  }

  public function getAvgEsp() {
    if (!$this->_calculated) {
      $this->calculate();
    }
    return $this->_avgEsp;
  }

}
