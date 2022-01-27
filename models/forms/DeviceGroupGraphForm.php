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

class DeviceGroupGraphForm extends Model {

  const DEFAULT_BINS = '150,1000';


  public $startDateTime, $endDateTime, $bins;

  /**
   * @return array the validation rules.
   */
  public function rules() {
    return [
      ['startDateTime', 'default', 'value' => date("Y-m-d H:i:s", time() - (60 * 60 * 24 * 14))],
      ['endDateTime', 'default', 'value' => date("Y-m-d H:i:s")],
      ['bins', 'validateBins'],
      [['startDateTime', 'endDateTime'], 'datetime', 'format' => 'php:Y-m-d H:i:s', 'message'=>'The format of {attribute} is invalid, this should be `YYYY-MM-DD hh:mm:ss`.'],
      ['bins', 'default', 'value' => \Yii::$app->session->get('bins', static::DEFAULT_BINS)],
    ];
  }

  public function validateBins($attribute, $params, $validator, $current) {
    $val = $this->$attribute;

    $bins = explode(',', $val);
    $previousBin = null;
    foreach ($bins as $bin) {
      if (!is_numeric($bin)) {
        $this->addError($attribute, "`" . $bin . "` is not a valid bin threshold (should be a number)");
        return false;
      }
      if ((int)$bin <= $previousBin) {
        $this->addError($attribute, "Bin threshold `" . $bin . "` should be larger than previous bin threshold `" . $previousBin . "`");
        return false;
      }

      $previousBin = (int)$bin;
    }
  }

  public function afterValidate() {
    \Yii::$app->session->set('bins', $this->bins);
  }
}
