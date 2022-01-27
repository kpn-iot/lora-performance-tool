<?php

use yii\db\Migration;

/**
 * Class m200706_134545_alter_gateways_lrr_id_column
 */
class m200706_134545_alter_gateways_lrr_id_column extends Migration {
  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->alterColumn('gateways', 'lrr_id', $this->string(50));
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->alterColumn('gateways', 'lrr_id', $this->string(8));
  }

}
