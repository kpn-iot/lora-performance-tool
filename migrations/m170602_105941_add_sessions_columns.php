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

class m170602_105941_add_sessions_columns extends Migration {

  public function up() {
    $this->addColumn('sessions', 'vehicle_type', 'VARCHAR(255) AFTER type');
    $this->addColumn('sessions', 'motion_indicator', 'VARCHAR(255) AFTER vehicle_type');
  }

  public function down() {
    $this->dropColumn('sessions', 'vehicle_type');
    $this->dropColumn('sessions', 'motion_indicator');
  }

}
