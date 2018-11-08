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
 * This is the model class for table "session_sets".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $nrSessions
 * @property string $created_at
 * @property string $updated_at
 *
 * @property SessionSetLink[] $sessionSetLinks
 * @property Session[] $sessions
 * @property lora\SessionCollection $sessionCollection
 */
class SessionSet extends ActiveRecord {

  private $_sessionCollection = null;

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'session_sets';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['name'], 'required'],
      [['description'], 'string'],
      [['created_at', 'updated_at'], 'safe'],
      [['name'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'description' => 'Description',
      'nrSessions' => 'Nr. Sessions',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
      'sessionCollection.frameCollection.nrDevices' => 'Nr Devices',
      'sessionCollection.frameCollection.coverage.avgGwCount' => 'Average GW Count',
      'sessionCollection.frameCollection.coverage.avgRssi' => 'Average RSSI',
      'sessionCollection.frameCollection.coverage.avgSnr' => 'Average SNR',
      'sessionCollection.frameCollection.nrFrames' => 'Nr Frames'
    ];
  }

  public function getSessionIdsString() {
    $ids = [];
    foreach ($this->sessions as $session) {
      $ids[] = $session->id;
    }
    return implode('.', $ids);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSessionSetLinks() {
    return $this->hasMany(SessionSetLink::className(), ['set_id' => 'id']);
  }
  
  /**
   * 
   * @return integer
   */
  public function getNrSessions() {
    return $this->getSessions()->count();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSessions() {
    return $this->hasMany(Session::className(), ['id' => 'session_id'])->viaTable('session_set_links', ['set_id' => 'id']);
  }

  public function getSessionCollection() {
    if ($this->_sessionCollection === null) {
//      $sessions = $this->getSessions()->with(['device', 'frames', 'lastFrame', 'firstFrame', 'frames.session', 'frames.session.device', 'frames.reception', 'frames.reception.gateway', 'frames.reception.frame', 'frames.reception.frame.session'])->all();
      $sessions = $this->getSessions()->with(['device'])->all();
      $this->_sessionCollection = new lora\SessionCollection($sessions);
    }
    return $this->_sessionCollection;
  }

}
