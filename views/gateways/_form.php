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

/* @var $this yii\web\View */
/* @var $model app\models\Gateway */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="gateway-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'latitude')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'type')->dropDownList($model::$typeOptions, ['prompt' => 'Unknown']) ?>

  <div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
