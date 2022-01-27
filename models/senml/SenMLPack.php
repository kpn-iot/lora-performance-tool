<?php
/*  _  __  ____    _   _
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 *
 * (c) 2019 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 *
 */

namespace app\models\senml;


class SenMLPack {
  public $devEUI, $baseTime, $records, $indexedRecords, $raw;

  public function __construct($string) {
    $json = json_decode($string, true);
    if ($json === null) {
      throw new \Exception("String is no valid JSON");
    }

    if (count($json) < 2 || !isset($json[0]['bn'])) {
      throw new \Exception("Not a valid SenML pack");
    }

    $baseRecord = $json[0];
    $re = '/^urn:dev:deveui:(\S+):$/mi';
    preg_match($re, $baseRecord['bn'], $matches);

    if (count($matches) === 0) {
      throw new \Exception("Base name should be of the form 'urn:dev:deveui:<deveui>:'");
    }
    $this->devEUI = strtoupper($matches[1]);
    $this->baseTime = (isset($baseRecord['bt'])) ? $baseRecord['bt'] : time();

    $this->records = [];
    $this->indexedRecords = [];
    for ($i = 1; $i < count($json); $i++) {
      $record = new SenMLRecord($json[$i]);
      $this->records[] = $record;
      $this->indexedRecords[$record->n] = $record;
    }

    return true;
  }

  public function getMeasurement($n, $raw = false) {
    if (!isset($this->indexedRecords[$n])) {
      return null;
    }
    if ($raw) {
      return $this->indexedRecords[$n];
    } else {
      return $this->indexedRecords[$n]->v;
    }
  }

}