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

class m170821_181356_add_gateways_columns extends Migration {

  public function up() {
    $this->addColumn('gateways', 'type', 'ENUM("sectorized","omni") AFTER lrr_id');
    $this->addColumn('gateways', 'deleted_at', 'DATETIME');
  }

  public function down() {
    $this->dropColumn('gateways', 'type');
    $this->dropColumn('gateways', 'deleted_at');
  }

}
