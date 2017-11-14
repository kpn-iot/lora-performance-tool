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

class m170320_141542_add_sessions_deleted_at_column extends Migration {

  public function up() {
    $this->addColumn('sessions', 'deleted_at', 'DATETIME DEFAULT NULL');
  }

  public function down() {
    $this->dropColumn('sessions', 'deleted_at');
  }

}
