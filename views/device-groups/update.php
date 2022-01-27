<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DeviceGroup */

$this->title = 'Update Device Group: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="device-group-update">

  <?= $this->render('_form', [
      'model' => $model,
  ]) ?>

</div>
