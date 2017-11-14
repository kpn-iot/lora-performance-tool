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

class m170512_135602_add_frames_channel_column extends Migration {

  public function up() {
    $this->addColumn('frames', 'channel', 'VARCHAR(10) AFTER gateway_count');
  }

  public function down() {
    $this->dropColumn('frames', 'channel');
  }

}
