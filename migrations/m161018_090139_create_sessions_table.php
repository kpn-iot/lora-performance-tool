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

class m161018_090139_create_sessions_table extends Migration {

  public function up() {
    $this->createTable('sessions', [
      'id' => $this->bigPrimaryKey(),
      'device_id' => $this->integer(),
      'description' => $this->text(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);
    $this->addForeignKey('fk_sessions_device_id', 'sessions', 'device_id', 'devices', 'id');
  }

  public function down() {
    $this->dropTable('sessions');
  }

}
