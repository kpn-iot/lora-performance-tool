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

use app\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Session */
/* @var $splitForm \app\models\SessionSplitForm */

$this->title = "Split " . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['/devices/index']];
$this->params['breadcrumbs'][] = ['label' => $model->device->name, 'url' => ['/devices/view', 'id' => $model->device_id]];
$this->params['breadcrumbs'][] = ['label' => 'Sessions', 'url' => ['index', 'SessionSearch[device_id]' => $model->device_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="well">
  <?php $form = ActiveForm::begin() ?>

  <?= $form->field($splitForm, 'frameCounter')->hint('Enter here the frame counter value (FCntUp) at which you want to start the new session') ?>

  <?= $form->field($splitForm, 'copyProperties')->checkbox() ?>

  <div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end() ?>

</div>

<?= $this->render('/_partials/geoloc-table', ['frameCollection' => $model->frameCollection]) ?>