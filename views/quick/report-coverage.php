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

$this->title = "Coverage report " . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Quicks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$frameCollection = $model->frameCollection;
?>

<p>
  <?= Html::a(Html::icon('equalizer') . ' Geoloc Report', ['report-geoloc', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>
  <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary hidden-print']) ?>
  <?= Html::a('Download file', ['file', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']); ?>
</p>

<?= $this->render('_detail', ['model' => $model]) ?>

<?= $this->render('/_partials/coverage-usage-graphs', ['frameCollection' => $frameCollection]) ?>
<?= $this->render('/_partials/coverage-time-graphs', ['frameCollection' => $frameCollection]) ?>

</div>
<div class="container-fluid">
  <?= $this->render('/_partials/coverage-table', ['frameCollection' => $frameCollection]) ?>
</div>
