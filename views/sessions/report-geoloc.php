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

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Session */

$this->title = "Geoloc report " . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['/devices/index']];
$this->params['breadcrumbs'][] = ['label' => $model->device->name, 'url' => ['/devices/view', 'id' => $model->device_id]];
$this->params['breadcrumbs'][] = ['label' => 'Sessions', 'url' => ['index', 'SessionSearch[device_id]' => $model->device_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
  <?= Html::a(Html::icon('stats') . ' Coverage Report', ['/sessions/report-coverage', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>
  <?= Html::a(Html::icon('map-marker') . ' Map', ['/map/index', 'session_id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>
  <?= Html::a(Html::icon('list') . ' Frames', ['/frames/index', 'FrameSearch[session_id]' => $model->id], ['class' => 'btn btn-link hidden-print']); ?>
  <?= Html::a(Html::icon('pencil') . ' Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary hidden-print']) ?>
  <?= Html::a(Html::icon('resize-full') . ' Split', ['split', 'id' => $model->id], ['class' => 'btn btn-default hidden-print']) ?>
  <?=
  Html::a(Html::icon('trash') . ' Delete', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger hidden-print',
    'data' => [
      'confirm' => 'Are you sure you want to delete this item?',
      'method' => 'post',
    ],
  ])
  ?>
</p>
<div class="row">
  <div class="col-sm-6">
    <?= $this->render('_detail', ['model' => $model]) ?>
  </div>
  <div class="col-sm-6" style="height:409px">
    <?= Html::a($this->render('/map/small', ['session_id' => $model->id]), ['/map/index', 'session_id' => $model->id]) ?>
  </div>
</div>


<hr />
<?= $this->render('/_partials/geoloc-pdf-cdf-graphs', ['stats' => $model->frameCollection->geoloc]) ?>
<hr />
<div class="row">
  <div class="col-sm-9">
    <?= $this->render('/_partials/geoloc-time-graph', ['frameCollection' => $model->frameCollection]) ?>
  </div>
  <div class="col-sm-3">
    <?= $this->render('/_partials/geoloc-per-gateway-count-table', ['geolocStats' => $model->frameCollection->geoloc]) ?>
  </div>
</div>
<hr />
</div>
<div class="container-fluid">
  <?= $this->render('/_partials/geoloc-table', ['frameCollection' => $model->frameCollection]) ?>
</div>
<div class="container">
