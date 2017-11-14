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
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SessionSet */

$this->title = $model->name . ' <small>Coverage report</small>';
$this->params['breadcrumbs'][] = ['label' => 'Session Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Coverage report';

$sessionCollection = $model->sessionCollection;
?>
<p>
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
  <?= Html::a(Html::icon('equalizer') . ' Geoloc report', ['report-geoloc', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>

</p>

<?=
DetailView::widget([
  'model' => $model,
  'attributes' => [
    'name',
    'description:ntext',
    'sessionCollection.frameCollection.nrDevices',
    'sessionCollection.frameCollection.coverage.avgGwCount',
    'sessionCollection.frameCollection.coverage.avgRssi',
    'sessionCollection.frameCollection.coverage.avgSnr'
  ],
])
?>

<?= $this->render('/_partials/coverage-usage-graphs', ['frameCollection' => $sessionCollection->frameCollection]) ?>

</div>
<div class="container-fluid">
  <?= $this->render('/_partials/coverage-table', ['frameCollection' => $sessionCollection->frameCollection]) ?>
</div>
