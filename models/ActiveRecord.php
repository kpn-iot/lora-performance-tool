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

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Description of MemberDbRecord
 *
 * @author Paul Marcelis
 */
class ActiveRecord extends \yii\db\ActiveRecord {

  public function behaviors() {
    return [
      [
        'class' => TimestampBehavior::className(),
        'value' => new Expression('UTC_TIMESTAMP()'),
      ],
    ];
  }

  /**
   * Additional function to get the table name with the database name prefixed
   *
   * @return string
   */
  public static function absTableName() {
    $dbName = static::_getDsnAttribute('dbname', static::getDb());
    $tableName = static::tableName();

    return "`{$dbName}`.{$tableName}";
  }

  /**
   * Get a DSN attribute from database connection
   *
   * @param string $name
   * @param \yii\db\Connection $dsn
   * @return null
   */
  private static function _getDsnAttribute($name, $db) {
    if (preg_match('/' . $name . '=([^;]*)/', $db->dsn, $match)) {
      return $match[1];
    } else {
      return null;
    }
  }

}
