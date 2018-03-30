<?php

/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2018 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

use yii\db\Migration;

/**
 * Class m180126_091705_create_locations_table
 */
class m180126_091705_create_locations_table extends Migration {

  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->createTable('locations', [
      'id' => $this->primaryKey(),
      'name' => $this->string()->notNull(),
      'description' => $this->text(),
      'latitude' => $this->string(15),
      'longitude' => $this->string(15),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);

    $this->addColumn('sessions', 'location_id', 'INT AFTER motion_indicator');
    $this->addForeignKey('fk_sessions_location_id', 'sessions', 'location_id', 'locations', 'id');
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->dropForeignKey('fk_sessions_location_id', 'sessions');
    $this->dropColumn('sessions', 'location_id');
    $this->dropTable('locations');
  }

}
