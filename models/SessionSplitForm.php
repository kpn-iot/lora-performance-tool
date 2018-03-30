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
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class SessionSplitForm extends Model {

  public $frameCounter;
  public $copyProperties = true;

  /**
   * @return array the validation rules.
   */
  public function rules() {
    return [
      // username and password are both required
      [['frameCounter'], 'required'],
      ['frameCounter', 'integer'],
      ['copyProperties', 'boolean']
    ];
  }

  public function attributeLabels() {
    return [
      'frameCounter' => 'FCntUp',
      'copyProperties' => 'Copy session properties'
    ];
  }

}
