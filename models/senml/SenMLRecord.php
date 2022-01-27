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


class SenMLRecord {
  public $n, $v, $u;

  public function __construct($object) {
    if (!isset($object['n'])) {
      throw new \Exception("Not a valid SenML record, `n` is missing");
    }

    $this->n = $object['n'];

    if (isset($object['v'])) {
      $this->v = $object['v'];
    } elseif (isset($object['vb'])) {
      $this->v = $object['vb'];
    } elseif (isset($object['vs'])) {
      $this->v = $object['vs'];
    } elseif (isset($object['vd'])) {
      $this->v = $object['vd'];
    } else {
      throw new \Exception("Not a valid SenML record, a value is missing");
    }

    if (isset($object['u'])) {
      $this->u = $object['u'];
    }

    return true;
  }

}