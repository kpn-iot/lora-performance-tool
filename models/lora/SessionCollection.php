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

use app\helpers\Html;

/**
 * @property string $name
 * @property string $description
 * @property array $frr
 * @property FrameCollection $frameCollection
 * @property \app\models\Session[] $sessions
 * @property string $idList
 * @property array $firstFrameLocSolveAccuracy
 */
class SessionCollection extends \yii\base\BaseObject {

  static $nrFirstFrames = 20;
  private $_sessions, $_name, $_description, $_frr, $_frameCollection, $_idList = null, $_firstFrameLocSolveAccuracy = null;

  public function __construct($sessions, $config = []) {

    $this->_sessions = $sessions;
    $frames = [];

    $this->_name = [];
    $this->_description = [];
    $this->_frr = ['scope' => 0, 'frr' => 0];
    foreach ($this->_sessions as $session) {
      $this->_name[] = $session->name;
      $this->_description[] = Html::a($session->name, ['/sessions/report-coverage', 'id' => $session->id]) . ": " . $session->frr . " frames of " . $session->scope . " received (" . $session->frrRel . ")";
      $this->_frr['scope'] += $session->scope;
      $this->_frr['frr'] += $session->frr;
      $frames = array_merge($frames, $session->frames);
    }
    $this->_name = implode(", ", $this->_name);
    $this->_description = "<ul><li>" . implode("</li><li>", $this->_description) . "</li></ul>";
    $this->_frr['frrRel'] = ($this->_frr['scope'] == 0) ? null : round(100 * $this->_frr['frr'] / $this->_frr['scope'], 2) . "%";
    $this->_frameCollection = new FrameCollection($frames);

    parent::__construct($config);
  }

  public function getSessions() {
    return $this->_sessions;
  }

  public function getName() {
    return $this->_name;
  }

  public function getDescription() {
    return $this->_description;
  }

  public function getFrr() {
    return $this->_frr;
  }

  public function getFrameCollection() {
    return $this->_frameCollection;
  }

  public function getIdList() {
    if ($this->_idList === null) {
      $ids = [];
      foreach ($this->_sessions as $session) {
        $ids[] = $session->id;
      }
      $this->_idList = implode('.', $ids);
    }
    return $this->_idList;
  }

  public function getFirstFrameLocSolveAccuracy() {
    if ($this->_firstFrameLocSolveAccuracy === null) {
      $firstFrames = [];
      foreach ($this->_sessions as $session) {
        $nrFirstFrames = min(count($session->frames), static::$nrFirstFrames);
        for ($i = 0; $i < $nrFirstFrames; $i++) {
          if (!isset($firstFrames[$i])) {
            $firstFrames[$i] = [];
          }
          $firstFrames[$i][] = $session->frames[$i];
        }
      }
      $firstFrameAvgAccuracy = [];
      foreach ($firstFrames as $frameSet) {
        $avgAccuracySum = 0;
        foreach ($frameSet as $frame) {
          $avgAccuracySum += $frame['distance'];
        }
        $firstFrameAvgAccuracy[] = $avgAccuracySum / count($frameSet);
      }
      $this->_firstFrameLocSolveAccuracy = $firstFrameAvgAccuracy;
    }
    return $this->_firstFrameLocSolveAccuracy;
  }

}
