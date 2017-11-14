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

class m161019_120630_create_api_log_table extends Migration {

  public function up() {
    $this->createTable('api_log', [
      'id' => $this->bigPrimaryKey(),
      'origin' => $this->string(),
      'query_string' => $this->text(),
      'body' => $this->text(),
      'comments' => $this->text(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);
  }

  public function down() {
    $this->dropTable('api_log');
  }

}
