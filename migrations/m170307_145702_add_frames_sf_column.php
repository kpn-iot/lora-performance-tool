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

use yii\db\Migration;

class m170307_145702_add_frames_sf_column extends Migration {

  public function up() {
    $this->addColumn('frames', 'sf', 'INT(2) AFTER gateway_count');
  }

  public function down() {
    $this->dropColumn('frames', 'sf');
  }

}
