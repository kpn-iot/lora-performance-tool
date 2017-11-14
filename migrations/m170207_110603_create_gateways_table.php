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

class m170207_110603_create_gateways_table extends Migration {

  public function up() {
    $this->createTable('gateways', [
      'id' => $this->primaryKey(),
      'lrr_id' => $this->string(8)->unique(),
      'latitude' => $this->string(15),
      'longitude' => $this->string(15),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);
  }

  public function down() {
    $this->dropTable('gateways');
  }

}
