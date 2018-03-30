<?php

use yii\db\Migration;

/**
 * Class m180330_123213_add_frame_location_radius_lora_column
 */
class m180330_123213_add_frame_location_radius_lora_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('frames', 'location_radius_lora', 'VARCHAR(15) AFTER location_age_lora');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('frames', 'location_radius_lora');
    }
}
