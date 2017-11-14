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
  <?= Html::a(Html::icon('equalizer') . ' Geoloc report', ['report-geoloc', 'id' => $sessionCollection->idList], ['class' => 'btn btn-link hidden-print']) ?>
  <?php if ($sessionCollection->frameCollection->nrDevices == 1): ?>
    <?= Html::a(Html::icon('map-marker') . ' Map', ['/map/index', 'session_id' => $sessionCollection->idList], ['class' => 'btn btn-link hidden-print']) ?>
  <?php endif ?>
</p>

<table id="w0" class="table table-striped table-bordered detail-view">
  <tbody>
    <tr><th>Description</th><td><?= $sessionCollection->description ?></td></tr>
    <tr><th>Frame reception ratio</th><td><?= $sessionCollection->frr['frr'] ?> frames of <?= $sessionCollection->frr['scope'] ?> received. (<?= $sessionCollection->frr['frrRel'] ?>)</td></tr>
    <tr><th>Nr devices</th><td><?= $sessionCollection->frameCollection->nrDevices ?></td></tr>
    <tr><th>Average gateway count</th><td><?= $sessionCollection->frameCollection->coverage->avgGwCount ?></td></tr>
    <tr><th>Average RSSI</th><td><?= $sessionCollection->frameCollection->coverage->avgRssi ?> dBm</td></tr>
    <tr><th>Average SNR</th><td><?= $sessionCollection->frameCollection->coverage->avgSnr ?> dB</td></tr>
  </tbody>
</table>

<?= $this->render('/_partials/coverage-usage-graphs', ['frameCollection' => $sessionCollection->frameCollection]) ?>
<?= $this->render('/_partials/coverage-time-graphs', ['frameCollection' => $sessionCollection->frameCollection]) ?>

</div>
<div class="container-fluid">
  <?= $this->render('/_partials/coverage-table', ['frameCollection' => $sessionCollection->frameCollection]) ?>
</div>
