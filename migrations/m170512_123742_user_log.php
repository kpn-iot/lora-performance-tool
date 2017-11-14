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

class m170512_123742_user_log extends Migration {

  public function up() {
    $this->createTable('user_log', [
      'id' => $this->primaryKey(),
      'ip_address' => $this->string(),
      'username' => $this->string(),
      'action' => $this->string(),
      'result' => $this->string(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);
  }

  public function down() {
    $this->dropTable('user_log');
  }

}
