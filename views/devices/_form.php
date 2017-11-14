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

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\data\Decoding;

/* @var $this yii\web\View */
/* @var $model app\models\Device */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="device-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'name')->textInput(['maxlength' => 100]) ?>

  <?= $form->field($model, 'device_eui')->textInput(['maxlength' => 16, 'placeholder' => '0000000000000000'])->hint('8 byte HEX key') ?>

  <?= $form->field($model, 'port_id')->textInput() ?>

  <?= $form->field($model, 'payload_type')->dropDownList(Decoding::getSupportedPayloadTypes(), ['prompt' => 'n.a.']) ?>

  <?= $form->field($model, 'as_id')->textInput(['maxlength' => 100])->hint('For downlink communication.') ?>

  <?= $form->field($model, 'lrc_as_key')->textInput(['maxlength' => 32])->hint('This should be a 16 Byte HEX key. ' . Html::a('Generate here', ['/site/keys'], ['target' => '_blank']) . '. <span class="bg-danger text-danger">LRC-AS Key is not required, but very much recommended, since it enables Token verification</span>') ?>

  <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

  <div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
