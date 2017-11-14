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

class m170602_081940_create_session_set_links_table extends Migration {

  public function up() {
    $this->createTable('session_set_links', [
      'set_id' => $this->integer()->notNull(),
      'session_id' => $this->bigInteger()->notNull(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime()
    ]);

    $this->addPrimaryKey('PK', 'session_set_links', ['set_id', 'session_id']);
    $this->addForeignKey('session_set_links_set_id', 'session_set_links', 'set_id', 'session_sets', 'id');
    $this->addForeignKey('session_set_links_session_id', 'session_set_links', 'session_id', 'sessions', 'id');
  }

  public function down() {
    $this->dropTable('session_set_links');
  }

}
