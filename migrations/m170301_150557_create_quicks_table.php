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

class m170301_150557_create_quicks_table extends Migration {

  public function up() {
    $this->createTable('quicks', [
      'id' => $this->primaryKey(),
      'name' => $this->string(100),
      'latitude' => $this->string(20),
      'longitude' => $this->string(20),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);
  }

  public function down() {
    $this->dropTable('quicks');
  }

}
