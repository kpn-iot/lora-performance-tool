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

use Yii;

/**
 * This is the model class for table "user_log".
 *
 * @property integer $id
 * @property string $ip_address
 * @property string $username
 * @property string $action
 * @property string $result
 * @property string $created_at
 * @property string $updated_at
 */
class UserLog extends ActiveRecord {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'user_log';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['created_at', 'updated_at'], 'safe'],
      [['ip_address', 'username', 'action', 'result'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'ip_address' => 'IP Address',
      'username' => 'Username',
      'action' => 'Action',
      'result' => 'Result',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  public static function log($action, $result = null, $username = null) {
    $new = new static();
    $new->ip_address = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
    if (Yii::$app->user->identity === null) {
      $new->username = $username;
    } else {
      $new->username = Yii::$app->user->identity->username;
    }
    $new->action = $action;
    $new->result = $result;
    if (!$new->save()) {
      throw new \yii\web\HttpException(400, 'User log could not be saved');
    }
  }

}
