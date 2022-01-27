<?php

use app\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Tabs;
use yii\helpers\Url;

/** @var $model \app\models\DeviceGroup */
/** @var $activeTab string */

?>
<p>
  <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
  <?= Html::a('Delete', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger',
    'data' => [
      'confirm' => 'Are you sure you want to delete this item?',
      'method' => 'post',
    ],
  ]) ?>
</p>

<?= DetailView::widget([
  'model' => $model,
  'attributes' => [
    'id',
    'name',
    'description:ntext',
    'created_at:datetime',
    'updated_at:datetime',
  ],
]) ?>

<br/>

<?php
$queryParams = ['id' => $model->id];
$formData = Yii::$app->request->get('DeviceGroupGraphForm');
if (isset($formData['startDateTime'])) {
  $queryParams['DeviceGroupGraphForm[startDateTime]'] = $formData['startDateTime'];
}
if (isset($formData['endDateTime'])) {
  $queryParams['DeviceGroupGraphForm[endDateTime]'] = $formData['endDateTime'];
}

?>

<?= Tabs::widget([
  'items' => [
    [
      'label' => 'Devices in Group',
      'url' => Url::to(['view'] + $queryParams),
      'active' => ($activeTab === 'view'),
    ],
    [
      'label' => 'Sessions',
      'url' => Url::to(['table'] + $queryParams),
      'active' => ($activeTab === 'table'),
    ],
    [
      'label' => 'Daily stats data',
      'url' => Url::to(['daily-stats'] + $queryParams),
      'active' => ($activeTab === 'daily-stats'),
    ],
    [
      'label' => 'Daily stats graphs',
      'url' => Url::to(['graphs'] + $queryParams),
      'active' => ($activeTab === 'graphs'),
    ],
    [
      'label' => 'Accuracy Histogram',
      'url' => Url::to(['histogram'] + $queryParams),
      'active' => ($activeTab === 'histogram'),
    ],
    [
      'label' => 'Raw data',
      'url' => Url::to(['raw'] + $queryParams),
      'active' => ($activeTab === 'raw'),
    ],
  ],
]) ?>

<br/>
