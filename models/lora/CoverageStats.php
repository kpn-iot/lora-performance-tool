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
 * @property array $espCdf
 */
class CoverageStats extends \yii\base\BaseObject {

  private $_frameCollection;
  private $_calculated = false;
  private $_avgRssi, $_avgGwCount = null, $_avgSnr, $_avgEsp, $_sfUsage, $_channelUsage, $_gwCountPdf, $_gwColors, $_timeline, $_graphs, $noFrames = null;
  private $_strongestEspList, $_espCdf = null;
  public static $colorList = ["#3366CC", "#DC3912", "#FF9900", "#109618", "#990099", "#3B3EAC", "#0099C6", "#DD4477", "#66AA00", "#B82E2E", "#316395", "#994499", "#22AA99", "#AAAA11", "#6633CC", "#E67300", "#8B0707", "#329262", "#5574A6", "#3B3EAC"];

  public function __construct(FrameCollection $frameCollection, $config = []) {
    $this->_frameCollection = $frameCollection;
    $this->noFrames = ($frameCollection->nrFrames === 0);
    parent::__construct($config);
  }

  private function calculate() {
    $frames = $this->_frameCollection->frames;

    $this->_sfUsage = ['7' => 0, '8' => 0, '9' => 0, '10' => 0, '11' => 0, '12' => 0];
    $this->_channelUsage = ['LC1' => 0, 'LC2' => 0, 'LC3' => 0, 'LC4' => 0, 'LC5' => 0, 'LC6' => 0, 'LC7' => 0, 'LC8' => 0,
        'LC9' => 0, 'LC10' => 0, 'LC11' => 0, 'LC12' => 0, 'LC13' => 0, 'LC14' => 0, 'LC15' => 0, 'LC16' => 0];
    $this->_gwCountPdf = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0];

    $this->_timeline = [];
    $strongestRssiList = [];
    $strongestSnrList = [];
    $strongestEspList = [];
    $gatewayIdList = [];
    $receptionPerGateway = [];

    $sfUsageCount = 0;
    $gwCountPdfCount = 0;
    $channelUsageCount = 0;

    foreach ($frames as $frame) {
      if ($frame['sf'] != '') {
        $sfUsageCount += 1;
        $this->_sfUsage[$frame['sf']] += 1;
      }
      if ($frame['gateway_count'] >= 1 && $frame['gateway_count'] <= 10) {
        $gwCountPdfCount += 1;
        $this->_gwCountPdf[$frame['gateway_count']] += 1;
      }
      if ($frame['channel'] != '' && isset($this->_channelUsage[$frame['channel']])) {
        $channelUsageCount += 1;
        $this->_channelUsage[$frame['channel']] += 1;
      }

      if (count($frame['reception']) > 0) {
        $strongestRssiList[] = $frame['reception'][0]['rssi'];
        $strongestSnrList[] = $frame['reception'][0]['snr'];
        $strongestEspList[] = round((float)$frame['reception'][0]['esp'], 2);
      }

      if (!$this->_frameCollection->isLarge) {
        $this->_timeline[] = [
            $frame['time'],
            (int)$frame['sf'],
            (int)$frame['gateway_count'],
            ($frame['channel'] != '') ? ((int)str_replace('LC', '', $frame['channel'])) : null
        ];

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

	if ($sfUsageCount > 0) {
		foreach ($this->_sfUsage as &$value) {
		  $value = round(100 * $value / $sfUsageCount, 1);
		}
	}
	if ($channelUsageCount > 0) {
		foreach ($this->_channelUsage as &$value) {
		  $value = round(100 * $value / $channelUsageCount, 1);
		}
	}
	if ($gwCountPdfCount > 0) {
		foreach ($this->_gwCountPdf as &$value) {
		  $value = round(100 * $value / $gwCountPdfCount, 1);
		}
	}

    $this->_avgRssi = (count($strongestRssiList) === 0) ? null : round(array_sum($strongestRssiList) / count($strongestRssiList), 2);
    $this->_avgSnr = (count($strongestSnrList) === 0) ? null : round(array_sum($strongestSnrList) / count($strongestSnrList), 2);
    $this->_avgEsp = (count($strongestEspList) === 0) ? null : round(array_sum($strongestEspList) / count($strongestEspList), 2);

    $this->_strongestEspList = $strongestEspList;

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

  public function getEspCdf() {
    if ($this->_espCdf === null) {
      if (count($this->_strongestEspList) === 0) {
        $this->_espCdf = false;
        return;
      }
      usort($this->_strongestEspList, function ($a, $b) {
        return ($a > $b);
      });

      $esps = [];
      foreach ($this->_strongestEspList as $esp) {
        if (!isset($esps['' . $esp])) {
          $esps['' . $esp] = 0;
        }
        $esps['' . $esp] += 1;
      }

      $cumsum = 0;
      foreach ($esps as $value => $sum) {
        $this->_espCdf[] = [
            'x' => (float)$value,
            'y' => $cumsum
        ];
        $cumsum += $sum;
        $this->_espCdf[] = [
            'x' => (float)$value,
            'y' => $cumsum
        ];
      }

      foreach ($this->_espCdf as &$point) {
        $point['y'] = round((100 * $point['y']) / $cumsum,2);
      }
    }
    return $this->_espCdf;
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
