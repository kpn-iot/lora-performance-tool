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

class m170821_191715_create_gateway_mutations_table extends Migration {

  public function up() {
    $this->createTable('gateway_mutations', [
      'id' => $this->bigPrimaryKey(),
      'gateway_id' => $this->integer(),
      'old_latitude' => $this->string(15),
      'old_longitude' => $this->string(15),
      'old_type' => $this->string(15),
      'old_state' => $this->string(30),
      'new_latitude' => $this->string(15),
      'new_longitude' => $this->string(15),
      'new_type' => $this->string(15),
      'new_state' => $this->string(30),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime(),
    ]);

    $this->addForeignKey('fk_gateway_mutations_gateway_id', 'gateway_mutations', 'gateway_id', 'gateways', 'id');
  }

  public function down() {
    $this->dropForeignKey('fk_gateway_mutations_gateway_id', 'gateway_mutations');
    $this->dropTable('gateway_mutations');
  }

}
