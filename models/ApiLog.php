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

/**
 * This is the model class for table "api_log".
 *
 * @property string $id
 * @property string $origin
 * @property string $query_string
 * @property string $body
 * @property string $comments
 * @property string $created_at
 * @property string $updated_at
 */
class ApiLog extends ActiveRecord {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'api_log';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['query_string', 'body', 'comments'], 'string'],
      [['created_at', 'updated_at'], 'safe'],
      [['origin'], 'string', 'max' => 255],
    ];
  }

  public function getDevice_eui() {
    preg_match("/LrnDevEui=([0-9A-Z]+)&/", $this->query_string, $output);
    return (count($output) == 2) ? $output[1] : null;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'origin' => 'Origin',
      'query_string' => 'Query String',
      'body' => 'Body',
      'comments' => 'Comments',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  public static function log($comments = null, $logPayload = true) {
    $new = new static();
    $new->origin = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
    $new->query_string = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : null;
    if ($logPayload) {
      $new->body = file_get_contents("php://input");
    }
    $new->comments = (is_array($comments)) ? json_encode($comments) : $comments;
    $new->save();
  }

}
