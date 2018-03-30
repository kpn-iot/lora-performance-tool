<?php

/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2018 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

namespace app\commands;

use yii\console\Controller;
use app\models\Session;

class SessionsController extends Controller {

  /**
   * 
   * @param type $message
   */
  public function actionUpdateProperties($force = false) {
    $sessions = Session::find()->with(['firstFrame', 'lastFrame', 'frames', 'properties', 'frames.session', 'frames.reception'])->orderBy(['id' => SORT_DESC]);

    $cntr = 1;
    foreach ($sessions->batch(25) as $models) {
      foreach ($models as $session) {
        $count = count($session->frames);
        if ($session->properties === null || $count !== $session->prop->nr_frames || $force) {
          echo "(" . ($cntr++) . ") " . $session->name . " (" . round(memory_get_usage() / 1000000) . "Mb) ";
          if ($count > 2000) {
            echo "[too large ({$count})]";
          } else {
            $session->updateProperties(false, false);
            gc_collect_cycles();
          }
          echo "\n";
        }
        $session = null;
      }
      echo '.';
      $models = null;
    }
    return 'Done';
  }

}
