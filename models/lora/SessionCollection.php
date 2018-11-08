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
use app\models\Gateway;
use app\models\Session;
use yii\base\BaseObject;

/**
 * @property string $name
 * @property string $description
 * @property array $frr
 * @property FrameCollection $frameCollection
 * @property \app\models\Session[] $sessions
 * @property string $idList
 * @property array $firstFrameLocSolveAccuracy
 * @property boolean $isLarge
 *
 * @property Session[] $_sessions
 */
class SessionCollection extends BaseObject {

  static $nrFirstFrames = 20;
  private $_sessions, $_name, $_description, $_frr, $_frameCollection = null, $_idList = null, $_firstFrameLocSolveAccuracy = null;

  public function __construct($sessions, $config = []) {
    $this->_sessions = $sessions;
    $this->_name = [];
    $this->_description = [];
    $this->_frr = ['scope' => 0, 'nrFrames' => 0];
    foreach ($this->_sessions as $session) {
      $this->_name[] = $session->name;
      $this->_description[] = Html::a($session->name, ['/sessions/report-coverage', 'id' => $session->id]) . ": " . $session->nrFrames . " frames of " . $session->scope . " received (" . $session->frr . ")";
      $this->_frr['scope'] += $session->scope;
      $this->_frr['nrFrames'] += $session->nrFrames;
    }
    $this->_name = implode(", ", $this->_name);
    $this->_description = "<ul><li>" . implode("</li><li>", $this->_description) . "</li></ul>";
    $this->_frr['frr'] = ($this->_frr['scope'] == 0) ? null : round(100 * $this->_frr['nrFrames'] / $this->_frr['scope'], 2) . "%";
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

  public function getIsLarge() {
    return $this->frameCollection->isLarge;
  }

  public function getFrameCollection() {
    if ($this->_frameCollection === null) {
      /** @var BareFrame[] $frames */
      $frames = [];
      $bareFrameFactory = new BareFrameFactory();
      foreach ($this->_sessions as $session) {
        $sessionFrames = $session->getFrames()->with('reception')->asArray()->all();
        foreach ($sessionFrames as $frame) {
          $frames[] = $bareFrameFactory->create($frame, $session);
        }
      }
      $this->_frameCollection = new FrameCollection($frames);
    }
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
        if (count($frameSet) == 0) {
          $firstFrameAvgAccuracy[] = 0;
          continue;
        }
        $tempFrameCollection = new FrameCollection($frameSet);
        $firstFrameAvgAccuracy[] = $tempFrameCollection->geoloc->average;
      }
      $this->_firstFrameLocSolveAccuracy = $firstFrameAvgAccuracy;
    }
    return $this->_firstFrameLocSolveAccuracy;
  }

}
