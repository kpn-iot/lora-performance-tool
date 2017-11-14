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
/* @var $model app\models\Quick */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="quick-form">

  <?php
  $form = ActiveForm::begin();
  ?>

  <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'file')->fileInput() ?>

  <?= $form->field($model, 'payload_type')->dropDownList(\app\components\data\Decoding::getSupportedPayloadTypes(), ['prompt' => 'n.a.']) ?>

  <?= $form->field($model, 'type')->dropDownList($model::$typeOptions) ?>

  <div class="row" id="quick-coordinates">
    <div class="col-sm-6">
      <?= $form->field($model, 'latitude')->textInput(['maxlength' => true])->hint('Location of static measurements. Leave empty if session does have valid GPS') ?>
    </div>
    <div class="col-sm-6">
      <?= $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>
    </div>
  </div>

  <div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>
  <script>
    $("#quick-type").change(intfs);
    $(document).ready(intfs);
    function intfs() {
      $("#quick-coordinates").css("display", ($("#quick-type").val() == 'static') ? 'block' : 'none');
    }
    ;
  </script>

</div>
