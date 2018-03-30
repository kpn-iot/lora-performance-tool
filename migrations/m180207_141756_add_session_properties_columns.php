<?php

use yii\db\Migration;

/**
 * Class m180207_141756_add_session_properties_columns
 */
class m180207_141756_add_session_properties_columns extends Migration {

  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->addColumn('session_properties', 'geoloc_accuracy_2d_distance', 'DECIMAL(7,2) AFTER geoloc_accuracy_90perc');
    $this->addColumn('session_properties', 'geoloc_accuracy_2d_direction', 'DECIMAL(7,2) AFTER geoloc_accuracy_2d_distance');
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->dropColumn('session_properties', 'geoloc_accuracy_2d_distance');
    $this->dropColumn('session_properties', 'geoloc_accuracy_2d_direction');
  }

}
