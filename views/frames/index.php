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

use yii\grid\GridView;
use kartik\select2\Select2;

/** @var $session null|app\models\Session */
/* @var $this yii\web\View */
/* @var $searchModel app\models\FrameSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['/devices']];
if ($session != null) {
  $this->title = 'Frames of ' . $session->name;

  $this->params['breadcrumbs'][] = ['label' => $session->device->name, 'url' => ['/devices/view', 'id' => $session->device_id]];
  $this->params['breadcrumbs'][] = ['label' => 'Sessions', 'url' => ['index', 'SessionSearch[device_id]' => $session->device_id]];
  $this->params['breadcrumbs'][] = ['label' => $session->name, 'url' => ['/sessions/view', 'id' => $session->id]];
  $this->params['breadcrumbs'][] = 'Frames';
} else {
  $this->title = 'Frames';
  $this->params['breadcrumbs'][] = ['label' => 'Sessions', 'url' => ['/sessions']];
  $this->params['breadcrumbs'][] = $this->title;
}

$columns = [
  [
    'label' => '#',
    'attribute' => 'id'
  ],
  [
    'label' => 'Session',
    'attribute' => 'session_id',
    'filter' => Select2::widget(['name' => 'FrameSearch[session_id]', 'value' => $searchModel->session_id, 'data' => $sessionsFilter, 'options' => ['placeholder' => '']]),
    'format' => 'raw',
    'value' => function ($data) {
      return $data->session->fullName;
    }
  ],
  'count_up',
  'payload_hex',
  'receptionInfo:raw',
  'informationArray:list',
  'coordinates:raw',
  'loraCoordinates:raw',
  'gateway_count',
  'channel',
  'sf',
  'time',
  'created_at:timeAgo',
  'created_at:dateTime'
];
?>
<style>
  table ul {
    margin: 0;
    padding: 0;
    list-style-position: inside;
  }
</style>
</div>
<div class="container-fluid">
  <div class="table-responsive">
    <?=
    GridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel' => $searchModel,
      'columns' => $columns,
    ]);
    ?>
  </div>
</div>
<div class="container">
