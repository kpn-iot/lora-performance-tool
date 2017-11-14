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

class m170602_081741_create_session_sets_table extends Migration {

  public function up() {
    $this->createTable('session_sets', [
      'id' => $this->primaryKey(),
      'name' => $this->string()->notNull(),
      'description' => $this->text(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);
  }

  public function down() {
    $this->dropTable('session_sets');
  }

}
