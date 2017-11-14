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

class m161018_084933_create_devices_table extends Migration {

  public function up() {
    $this->createTable('devices', [
      'id' => $this->primaryKey(),
      'name' => $this->string(100)->notNull(),
      'device_eui' => $this->string(16)->notNull(),
      'port_id' => $this->integer()->notNull(),
      'payload_type' => $this->string(30),
      'as_id' => $this->string(100),
      'lrc_as_key' => $this->string(32),
      'description' => $this->text(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);
    $this->createIndex('pk_devices', 'devices', ['device_eui', 'port_id'], true);
  }

  public function down() {
    $this->dropTable('devices');
  }

}
