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

$locationsRaw = app\models\Location::find()->orderBy(['name' => SORT_ASC])->all();
$locations = [];
foreach ($locationsRaw as $loc) {
  $locations[$loc->id] = $loc->name;
}
?>

<div class="session-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

  <?= $form->field($model, 'type')->dropDownList($model::$typeOptions) ?>

  <?= $form->field($model, 'location_report_source')->dropDownList($model::$locationReportSourceOptions) ?>

  <?= $form->field($model, 'location_id')->dropDownList($locations, ['prompt' => 'Manual location']) ?>

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
    $("#session-location_id").change(intfs);
    $(document).ready(intfs);
    function intfs() {
      $(".field-session-location_id").css("display", ($("#session-type").val() == 'static') ? 'block' : 'none');
      $(".field-session-location_report_source").css("display", ($("#session-type").val() == 'static') ? 'block' : 'none');
      $("#session-coordinates").css("display", ($("#session-type").val() == 'static' && $("#session-location_id").val() == '') ? 'block' : 'none');
    }
    ;
  </script>

</div>
