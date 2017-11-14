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

class m161018_093327_create_frames_table extends Migration {

  public function up() {
    $this->createTable('frames', [
      'id' => $this->bigPrimaryKey(),
      'session_id' => $this->bigInteger()->notNull(),
      'count_up' => $this->bigInteger()->notNull(),
      'payload_hex' => $this->text(),
      'information' => $this->text(),
      'latitude' => $this->string(15),
      'longitude' => $this->string(15),
      'gateway_count' => $this->integer(),
      'time' => $this->string(100),
      'latitude_lora' => $this->string(15),
      'longitude_lora' => $this->string(15),
      'location_age_lora' => $this->integer(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);
    $this->addForeignKey('fk_frames_session_id', 'frames', 'session_id', 'sessions', 'id');
  }

  public function down() {
    $this->dropForeignKey('fk_frames_session_id', 'frames');
    $this->dropTable('frames');
  }

}
