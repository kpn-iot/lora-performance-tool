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
 * Class m171218_164837_create_session_properties_table
 */
class m171218_164837_create_session_properties_table extends Migration {

  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->createTable('session_properties', [
      'session_id' => $this->bigInteger()->notNull(),
      'frame_counter_first' => $this->integer()->unsigned(),
      'frame_counter_last' => $this->integer()->unsigned(),
      'session_date_at' => $this->dateTime(),
      'first_frame_at' => $this->dateTime(),
      'last_frame_at' => $this->dateTime(),
      'nr_frames' => $this->integer()->unsigned(),
      'frame_reception_ratio' => $this->decimal(5, 2),
      'gateway_count_average' => $this->decimal(5, 2),
      'rssi_average' => $this->decimal(5, 2),
      'snr_average' => $this->decimal(5, 2),
      'esp_average' => $this->decimal(5, 2),
      'interval' => $this->bigInteger()->unsigned(),
      'runtime' => $this->bigInteger()->unsigned(),
      'sf_min' => $this->integer()->unsigned(),
      'sf_max' => $this->integer()->unsigned(),
      'geoloc_accuracy_median' => $this->decimal(7, 2),
      'geoloc_accuracy_average' => $this->decimal(7, 2),
      'geoloc_accuracy_90perc' => $this->decimal(7, 2),
      'geoloc_success_rate' => $this->decimal(5, 2),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);

    $this->addPrimaryKey('PRIMARY KEY', 'session_properties', 'session_id');
    $this->addForeignKey('fk_session_properties_session_id', 'session_properties', 'session_id', 'sessions', 'id');
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->dropForeignKey('fk_session_properties_session_id', 'session_properties');
    $this->dropTable('session_properties');
  }

}
