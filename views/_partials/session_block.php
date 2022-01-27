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
use yii\helpers\Url;

if (!isset($hideDevice)) {
  $hideDevice = false;
}

$attributes = [];

if (!$hideDevice) {
  $attributes[] = [
    'label' => 'Device',
    'format' => 'raw',
    'attribute' => 'device.name',
    'value' => function($data) {
      return Html::a($data->device->name, ['/devices/view', 'id' => $data->device_id]);
    }
  ];
}

$attributes = array_merge($attributes, [
  'nrFrames',
  'frr',
  [
    'attribute' => 'avgGwCount',
    'label' => 'Avg. GW Count'
  ],
  [
    'label' => 'Location Median Accuracy',
    'value' => function($data) {
      if ($data->prop->geoloc_accuracy_median === null) {
        return null;
      }
      return Yii::$app->formatter->asDistance($data->prop->geoloc_accuracy_median);
    },
    'format' => 'raw'
  ],
  'locSolveSuccess:raw',
  [
    'label' => 'Last frame',
    'attribute' => 'prop.last_frame_at',
    'format' => 'dateTime'
  ],
  [
    'label' => 'Runtime',
    'attribute' => 'runtime'
  ],
  [
    'label' => 'Last activity',
    'attribute' => 'prop.last_frame_at',
    'format' => 'timeAgo'
  ]
  ]);
?>

<div class="panel panel-default">
  <div class="panel-heading">
    <span class="btn-group btn-group-xs pull-right" style="margin-top:-2px">
      <a class="btn btn-default" data-toggle="popover" data-placement="top" data-container="body" data-html="true" role="button" 
         data-content="<iframe src='<?= Url::to(['/map/popover', 'session_id' => $session->id]) ?>' style='border:none;margin: -9px -14px;width:272px;height:300px'></iframe>"><?= Html::icon('modal-window') ?></a>
         <?= Html::a(Html::icon('stats'), ['/sessions/report-coverage', 'id' => $session->id], ['class' => 'btn btn-default']) ?>
         <?= Html::a(Html::icon('equalizer'), ['/sessions/report-geoloc', 'id' => $session->id], ['class' => 'btn btn-default']) ?>
         <?= Html::a(Html::icon('map-marker'), ['/map/index', 'session_id' => $session->id], ['class' => 'btn btn-default']) ?>
        <?= Html::a(Html::icon('export'), ['/sessions/export', 'id' => $session->id], ['class' => 'btn btn-default']) ?>
    </span>
    <span class="pull-left" style="margin-right:7px">
      <?= $session->typeIcon ?>
    </span>
    <h4 class="panel-title"><?= Html::a($session->name, ['/sessions/update', 'id' => $session->id]) ?></h4>
  </div>
  <?=
  yii\widgets\DetailView::widget([
    'model' => $session,
    'attributes' => $attributes
  ])
  ?>
</div>
