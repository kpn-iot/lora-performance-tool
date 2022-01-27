<?php

use yii\db\Migration;

/**
 * Class m190729_083015_add_sessions_geoloc_source_column
 */
class m190729_083015_add_sessions_geoloc_source_column extends Migration {
  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->addColumn('sessions', 'location_report_source', $this->string(20)->notNull()->defaultValue('lora')->after('longitude'));
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->dropColumn('sessions', 'location_report_source');
  }

}
