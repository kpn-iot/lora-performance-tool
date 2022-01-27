<?php

use yii\db\Migration;

/**
 * Class m201123_205010_introduce_device_groups
 */
class m201123_205010_introduce_device_groups extends Migration {
  /**
   * {@inheritdoc}
   */
  public function safeUp() {
    $this->createTable('device_groups', [
        'id' => $this->primaryKey(),
        'name' => $this->string()->notNull(),
        'description' => $this->text(),
        'created_at' => $this->dateTime(),
        'updated_at' => $this->dateTime(),
    ]);

    $this->createTable('device_group_links', [
        'group_id' => $this->integer()->notNull(),
        'device_id' => $this->integer()->notNull(),
        'created_at' => $this->dateTime(),
        'updated_at' => $this->dateTime(),
    ]);

    $this->addPrimaryKey('PK', 'device_group_links', ['group_id', 'device_id']);
    $this->addForeignKey('device_group_links_group_id', 'device_group_links', 'group_id', 'device_groups', 'id');
    $this->addForeignKey('device_group_links_device_id', 'device_group_links', 'device_id', 'devices', 'id');

  }

  /**
   * {@inheritdoc}
   */
  public function safeDown() {
    $this->dropTable('device_group_links');
    $this->dropTable('device_groups');
  }

}
