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
 * This is the model class for table "session_set_links".
 *
 * @property integer $set_id
 * @property string $session_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Session $session
 * @property SessionSet $set
 */
class SessionSetLink extends ActiveRecord {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'session_set_links';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['set_id', 'session_id'], 'required'],
      [['set_id', 'session_id'], 'integer'],
      [['created_at', 'updated_at'], 'safe'],
      [['session_id'], 'exist', 'skipOnError' => true, 'targetClass' => Session::className(), 'targetAttribute' => ['session_id' => 'id']],
      [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => SessionSet::className(), 'targetAttribute' => ['set_id' => 'id']],
      [['set_id', 'session_id'], 'unique', 'targetAttribute' => ['set_id', 'session_id'], 'message' => 'There can only be one link between a set and a session.'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'set_id' => 'Set ID',
      'session_id' => 'Session ID',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSession() {
    return $this->hasOne(Session::className(), ['id' => 'session_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSet() {
    return $this->hasOne(SessionSet::className(), ['id' => 'set_id']);
  }

}
