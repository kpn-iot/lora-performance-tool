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

/**
 * Class m171108_192245_add_quick_columns
 */
class m171108_192245_add_quick_columns extends Migration {

  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->addColumn('quicks', 'type', 'ENUM("moving", "static") NOT NULL DEFAULT "static" AFTER name');
    $this->addColumn('quicks', 'payload_type', $this->text(30)->after('longitude'));
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->dropColumn('quicks', 'payload_type');
    $this->dropColumn('quicks', 'type');
  }

}
