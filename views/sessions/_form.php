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
/* @var $model app\models\Session */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="session-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

  <?= $form->field($model, 'type')->dropDownList($model::$typeOptions) ?>

  <div class="row" id="session-coordinates">
    <div class="col-sm-6">
      <?= $form->field($model, 'latitude')->hint('Location of static measurements. Leave empty if session does have valid GPS') ?>
    </div>
    <div class="col-sm-6">
      <?= $form->field($model, 'longitude') ?>
    </div>
  </div>

  <?= $form->field($model, 'vehicle_type')->dropDownList($model::$vehicleTypeOptions) ?>

  <?= $form->field($model, 'motion_indicator')->dropDownList($model::$motionIndicatorOptions) ?>

  <div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>
  <script>
    $("#session-type").change(intfs);
    $(document).ready(intfs);
    function intfs() {
      $("#session-coordinates").css("display", ($("#session-type").val() == 'static') ? 'block' : 'none');
    }
    ;
  </script>

</div>
