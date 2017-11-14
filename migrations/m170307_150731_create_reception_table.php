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

class m170307_150731_create_reception_table extends Migration {

  public function up() {
    $this->createTable('reception', [
      'frame_id' => $this->bigInteger()->notNull(),
      'gateway_id' => $this->integer()->notNull(),
      'rssi' => $this->integer(),
      'snr' => $this->integer(),
      'esp' => $this->float(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);

    $this->addPrimaryKey('PK', 'reception', ['frame_id', 'gateway_id']);

    $this->addForeignKey('fk_reception_frame_id', 'reception', 'frame_id', 'frames', 'id');
    $this->addForeignKey('fk_reception_gateway_id', 'reception', 'gateway_id', 'gateways', 'id');
  }

  public function down() {
    $this->dropForeignKey('fk_reception_frame_id', 'reception');
    $this->dropForeignKey('fk_reception_gateway_id', 'reception');
    $this->dropTable('reception');
  }

}
