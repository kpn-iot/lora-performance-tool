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

class m170512_141854_add_sessions_columns extends Migration {

  public function up() {
    $this->addColumn('sessions', 'type', 'ENUM("moving", "static") NOT NULL DEFAULT "moving" AFTER description');
    $this->addColumn('sessions', 'latitude', 'VARCHAR(15) AFTER type');
    $this->addColumn('sessions', 'longitude', 'VARCHAR(15) AFTER latitude');
  }

  public function down() {
    $this->dropColumn('sessions', 'type');
    $this->dropColumn('sessions', 'latitude');
    $this->dropColumn('sessions', 'longitude');
  }

}
