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

use yii\base\Model;
use yii\web\UploadedFile;

class GatewayUpload extends Model {

  public $file, $type;
  public static $typeOptions = ['excel' => 'CSV generated from Gateway tab in Excel from MCP', 'export' => 'Export from log files'];

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['file', 'type'], 'required'],
      [['file'], 'file'],
      ['type', 'in', 'range' => array_keys(static::$typeOptions)]
    ];
  }

  public function beforeValidate() {
    if (($file = UploadedFile::getInstance($this, 'file')) != null) {
      $this->file = $file;
    }
    return parent::beforeValidate();
  }

}
