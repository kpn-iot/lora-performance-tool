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
/* @var $sessionCollection app\models\lora\SessionCollection */

$this->title = $sessionCollection->name;
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['/devices/index']];
$this->params['breadcrumbs'][] = ['label' => 'Sessions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
  <?= Html::a(Html::icon('stats') . ' Coverage report', ['report-coverage', 'id' => $sessionCollection->idList], ['class' => 'btn btn-link hidden-print']) ?>
  <?php if ($sessionCollection->frameCollection->nrDevices == 1): ?>
    <?= Html::a(Html::icon('map-marker') . ' Map', ['/map/index', 'session_id' => $sessionCollection->idList], ['class' => 'btn btn-link hidden-print']) ?>
  <?php endif ?>
</p>

<table id="w0" class="table table-striped table-bordered detail-view">
  <tbody>
    <tr><th>Description</th><td><?= $sessionCollection->description ?></td></tr>
    <tr><th>Frame reception ratio</th><td><?= $sessionCollection->frr['frr'] ?> frames of <?= $sessionCollection->frr['scope'] ?> received. (<?= $sessionCollection->frr['frrRel'] ?>)</td></tr>
    <tr><th>Nr devices</th><td><?= $sessionCollection->frameCollection->nrDevices ?></td></tr>
    <tr><th>Average LocSolve Accuracy</th><td><?= Yii::$app->formatter->asDecimal($sessionCollection->frameCollection->geoloc->average, 1) ?> m</td></tr>
    <tr><th>Average LocSolve Success</th><td><?= round($sessionCollection->frameCollection->geoloc->percentageNrLocalisations * 100) ?>%</td></tr>
  </tbody>
</table>

<hr />
<?= $this->render('/_partials/geoloc-pdf-cdf-graphs', ['stats' => $sessionCollection->frameCollection->geoloc]) ?>
<hr />
<?= $this->render('/_partials/geoloc-first-frames', ['avgDistances' => $sessionCollection->firstFrameLocSolveAccuracy]) ?>
<hr />
<?= $this->render('/_partials/geoloc-time-graph', ['frameCollection' => $sessionCollection->frameCollection]) ?>
<hr />
</div>
<div class="container-fluid">
  <?= $this->render('/_partials/geoloc-table', ['frameCollection' => $sessionCollection->frameCollection]) ?>
</div>
<div class="container">
