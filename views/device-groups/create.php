<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DeviceGroup */

$this->title = 'Create Device Group';
$this->params['breadcrumbs'][] = ['label' => 'Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-group-create">

  <?= $this->render('_form', [
      'model' => $model,
  ]) ?>

</div>
