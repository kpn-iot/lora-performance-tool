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
 * Class m180119_155457_add_devices_autosplit_column
 */
class m180119_155457_add_devices_autosplit_column extends Migration {

  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->addColumn("devices", "autosplit", "TINYINT(1) NOT NULL DEFAULT 1 AFTER description");
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->dropColumn("devices", "autosplit");
  }

}
