<?php

/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2018 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

namespace app\models\forms;

use app\models\Frame;
use app\models\Session;
use yii\base\Model;

class SessionMergeForm extends Model {

  public $sessionIdList;
  public $targetSessionId;

  private $_sessionIdArray;

  /**
   * @return array the validation rules.
   */
  public function rules() {
    return [
      // username and password are both required
        [['sessionIdList', 'targetSessionId'], 'required'],
        ['targetSessionId', 'integer'],
        ['sessionIdList', 'validateList'],
        ['targetSessionId', 'validateTarget']
    ];
  }

  public function validateList($attribute) {
    $this->_sessionIdArray = explode(',', $this->$attribute);
    foreach ($this->_sessionIdArray as &$item) {
      $item = trim($item);
    }

    if (count($this->_sessionIdArray) < 2) {
      return $this->addError($attribute, 'You should provide more than one session ID in the list.');
    }

    /** @var Session[] $sessionRecords */
    $sessionRecords = Session::find()->with('device')->andWhere(['id' => $this->_sessionIdArray])->asArray()->all();
    $devices = [];
    foreach ($sessionRecords as $session) {
      if (!isset($devices[$session['device_id']])) {
        $devices[$session['device_id']] = $session['device']['name'];
      }
    }

    if (count($devices) > 1) {
      return $this->addError($attribute, 'Sessions are from multiple devices: ' . implode(', ', $devices));
    }
  }

  public function validateTarget($attribute) {
    if (!in_array($this->$attribute, $this->_sessionIdArray)) {
      $this->addError($attribute, 'Target session ID should be in session ID list.');
    }
  }

  public function execute() {
    // only execute when validated
    if (!$this->validate()) {
      return false;
    }

    // update all frames from the sessions that should be merged in the target session
    $sessionsIdsToMerge = array_diff($this->_sessionIdArray, [$this->targetSessionId]);
    Frame::updateAll(['session_id' => $this->targetSessionId], ['session_id' => $sessionsIdsToMerge]);

    // update properties of sessions and soft-delete all non-target sessions
    /** @var Session[] $sessionRecordsToMerge */
    $sessionRecordsToMerge = Session::find()->andWhere(['id' => $this->_sessionIdArray])->all();
    foreach ($sessionRecordsToMerge as $session) {
      if ($session->id == $this->targetSessionId) {
        $session->updateProperties(false, true);
      } else {
        $session->delete();
      }
    }

    return true;
  }

  public function attributeLabels() {
    return [
        'sessionIdList' => 'Session ID list',
        'targetSessionId' => 'Target Session ID'
    ];
  }

}
