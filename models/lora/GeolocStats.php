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

use app\models\Frame;

/**
 * @property integer $average
 * @property integer $nrMeasurements
 * @property integer $nrLocalisations
 * @property integer $percentageNrLocalisations
 * @property array $pdf
 * @property array $cdf
 * @property array $timeGraphs
 * @property integer $perc90point
 */
class GeolocStats extends \yii\base\BaseObject {

  private $_frameCollection, $_measurementFrames;
  private $_average, $_nrMeasurements, $_nrLocalisations, $_percentageNrLocalisations, $_pdf = null, $_cdf = null, $_timeGraphs = null, $_perc90point = null;

  public function __construct(FrameCollection $frameCollection, $config = []) {
    $this->_frameCollection = $frameCollection;
    $this->_measurementFrames = [];

    $measurementCount = 0;
    $measurementSum = 0;

    $noNewLocalisationCount = 0;
    $localisationCount = 0;

    foreach ($frameCollection->frames as $frame) {
      if ($frame['location_age_lora'] < Frame::$locationAgeThreshold) { // new localisation
        $localisationCount += 1;
      } elseif ($frame['latitude_lora'] !== null && $frame['longitude_lora'] !== null) { // contains lora location values, not new
        $noNewLocalisationCount += 1;
        continue;
      } else { // no lora location values
        continue;
      }

      if (!isset($frame['distance']) || $frame['distance'] === null) { //no localisation
        continue;
      }

      // store frame with localisation and correct distance(accuracy in a separate array to be ordered for CDF)
      $this->_measurementFrames[] = $frame;

      $measurementCount += 1;
      $measurementSum += $frame['distance'];
    }

    $this->_nrMeasurements = $measurementCount; //correct gps + correct geoloc
    $this->_nrLocalisations = $localisationCount; //correct geoloc
    $this->_percentageNrLocalisations = (($localisationCount + $noNewLocalisationCount) == 0) ? 0 : ($localisationCount / ($localisationCount + $noNewLocalisationCount));

    $this->_average = ($measurementCount == 0) ? null : $measurementSum / $measurementCount;

    if ($measurementCount === 0) {
      return;
    }

    usort($this->_measurementFrames, function($a, $b) {
      return $a['distance'] > $b['distance'];
    });


    parent::__construct($config);
  }

  function getTimeGraphs() {
    if ($this->_timeGraphs === null) {
      $columns = [];
      $lines = [];
      $colSkip = 0;
      foreach ($this->_frameCollection->framesPerDevice as $deviceEui => $groupedFramesList) {
        $columns[] = $deviceEui;
        foreach ($groupedFramesList as $frame) {
          if ($frame['location_age_lora'] < Frame::$locationAgeThreshold && $frame['distance'] !== null) {
            $newLine = [$frame['created_at']];
            for ($i = 0; $i < $colSkip; $i++) {
              $newLine[] = null;
            }
            $newLine[] = $frame['distance'];
            for ($i = $colSkip + 1; $i < $this->_frameCollection->nrDevices; $i++) {
              $newLine[] = null;
            }
            $lines[] = $newLine;
          }
        }
        $colSkip += 1;
      }
      $this->_timeGraphs = [
        'columns' => $columns,
        'lines' => $lines
      ];
    }
    return $this->_timeGraphs;
  }

  public function getAverage() {
    return $this->_average;
  }

  public function getNrMeasurements() {
    return $this->_nrMeasurements;
  }

  public function getNrLocalisations() {
    return $this->_nrLocalisations;
  }

  public function getPercentageNrLocalisations() {
    return $this->_percentageNrLocalisations;
  }

  public function getPdf() {
    if ($this->_pdf === null) {
      $pdfBinValues = [50, 100, 150, 200, 250, 300];
      $pdfBinSize = 100;
      $pdfBinMax = 1500;
      for ($i = end($pdfBinValues) + $pdfBinSize; $i <= $pdfBinMax; $i += $pdfBinSize) {
        $pdfBinValues[] = $i;
      }
      $pdfBinValues[] = 1000000;

      $pdfBins = [];
      foreach ($this->_measurementFrames as $frame) {
        $binNr = count($pdfBinValues) - 1;
        foreach ($pdfBinValues as $nr => $bin) {
          if ($frame['distance'] < $bin) {
            $binNr = $nr;
            break;
          }
        }

        if (!isset($pdfBins[$binNr])) {
          $pdfBins[$binNr] = 0;
        }
        $pdfBins[$binNr] += 1;
      }

      // create pdf
      $this->_pdf = [];
      foreach ($pdfBinValues as $binId => $bin) {
        if ($binId == 0) {
          $label = '0-' . $bin . ' m';
        } elseif ($binId == count($pdfBinValues) - 1) {
          $label = $pdfBinValues[$binId - 1] . '+ m';
        } else {
          $label = $pdfBinValues[$binId - 1] . '-' . $bin . ' m';
        }

        $this->_pdf[$label] = (isset($pdfBins[$binId])) ? $pdfBins[$binId] : 0;
      }
    }
    return $this->_pdf;
  }
  
  private function cdf() {
      $this->_cdf = [
        ['x' => 0, 'y' => 0]
      ];
      $cumsum = 0;
      foreach ($this->_measurementFrames as $frame) {
        $this->_cdf[] = [
          'x' => round($frame['distance'], 2),
          'y' => $cumsum
        ];

        $cumsum += 1;

        $this->_cdf[] = [
          'x' => round($frame['distance'], 2),
          'y' => $cumsum
        ];
      }
      foreach ($this->_cdf as &$point) {
        $point['y'] = (100 * $point['y']) / $cumsum;
        if ($point['y'] >= 90 && $this->_perc90point === null) {
          $this->_perc90point = $point['x'];
        }
      }
  }

  public function getCdf() {
    if ($this->_cdf === null) {
      $this->cdf();
    }
    return $this->_cdf;
  }
  
  public function getPerc90point() {
    if ($this->_perc90point === null) {
      $this->cdf();
    }
    return $this->_perc90point;
  }

}
