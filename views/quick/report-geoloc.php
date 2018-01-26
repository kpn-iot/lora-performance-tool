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
/* @var $model app\models\Quick */

$this->title = "Geoloc report " . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Quicks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p>

  <?= Html::a(Html::icon('stats') . ' Coverage report', ['report-coverage', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>

  <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary hidden-print']) ?>
  <?=
  Html::a('Delete', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger hidden-print',
    'data' => [
      'confirm' => 'Are you sure you want to delete this item?',
      'method' => 'post',
    ],
  ])
  ?>
  <?= Html::a('Download file', ['file', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']); ?>
</p>

<?= $this->render('_detail', ['model' => $model]) ?>

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
