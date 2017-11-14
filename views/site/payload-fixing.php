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

use yii\bootstrap\ActiveForm;
use app\models\SessionSearch;
use yii\bootstrap\Html;

$this->title = "Update payload decoding";
$this->params['breadcrumbs'][] = $this->title;

$sessionModel = new SessionSearch();
?>

<?php $form = ActiveForm::begin() ?>

<?= $form->field($sessionModel, 'id')->dropDownList($sessions, ['prompt' => 'Select session to decode again']) ?>
<p>
  <label for="x">Or give a comma separated list of session ids</label>
  <input type="text" class='form-control' name="list" id='x'/>
</p>

<?= Html::submitButton('Fix payload decoding of session', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end() ?>