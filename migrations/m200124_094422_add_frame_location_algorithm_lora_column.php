<?php

use yii\db\Migration;

/**
 * Class m200124_094422_add_frame_location_algorithm_lora_column
 */
class m200124_094422_add_frame_location_algorithm_lora_column extends Migration {
  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->addColumn('frames', 'location_algorithm_lora', $this->string(20)->after('location_radius_lora'));
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->dropColumn('frames', 'location_algorithm_lora');
  }
}
