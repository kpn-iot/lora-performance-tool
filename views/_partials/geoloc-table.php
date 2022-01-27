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

/* @var $this yii\web\View */

/* @var $frameCollection app\models\lora\FrameCollection */

use app\models\Frame;

$this->registerCss("@media print {
  a[href]:after { content: none; }
  tr.danger > td, td.danger { background-color: #f2dede !important; }
  tr.info > td, td.info { background-color: #d9edf7 !important; }
  .table td { background-color: transparent !important; }
}");

$nrDevices = $frameCollection->nrDevices;
$withDeviceEui = ($nrDevices > 1);
$frames = $frameCollection->frames;
?>
<p class="hidden-print">
  A <span class="bg-info">blue line</span> indicates that for that frame a new GeoLoc result is received from the
  LocSolver. A <span class="bg-danger">red line</span> indicates that frame is not received.
</p>

<div class="table-responsive">
  <table class="table table-condensed table-bordered">
    <thead>
    <tr>
      <?php if ($withDeviceEui): ?>
        <th colspan="2"></th>
      <?php else: ?>
        <th></th>
      <?php endif ?>
      <th colspan="2" class="text-center">GPS</th>
      <th colspan="5" class="text-center">GeoLoc</th>
      <th colspan="2" class="text-center">LocDiff Info</th>
      <th colspan="3" class="text-center">Radio Info</th>
      <th colspan="5" class="text-center">GW Info</th>
      <th></th>
    </tr>
    <tr>
      <?php if ($withDeviceEui): ?>
        <th>DevEUI</th>
      <?php endif ?>
      <th class="text-right">FCntUp</th>
      <th>Lat</th>
      <th>Lng</th>
      <th>Lat</th>
      <th>Lng</th>
      <th class="text-right">Radius [m]</th>
      <th class="text-right">GeoLoc<br/>Relative<br/> Age [s]</th>
      <th class="text-right">GeoLoc<br/>Algorithm</th>
      <th class="text-right">Distance [m]</th>
      <th class="text-right">Direction</th>
      <th class="text-right">SF</th>
      <th class="text-right">Channel</th>
      <th class="text-right">GW Count</th>
      <th class="text-right">Best GW</th>
      <th class="text-right">Distance to GW [m]</th>
      <th class="text-right">RSSI</th>
      <th class="text-right">SNR</th>
      <th class="text-right">ESP</th>
      <th class="text-right">Timestamp</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($frames as $index => $frame): ?>
      <?php if ($nrDevices == 1 && $index > 0 && ($frame['count_up'] != $frames[$index - 1]['count_up'] + 1)): ?>
        <?php for ($i = $frames[$index - 1]['count_up'] + 1; $i < $frame['count_up']; $i++): ?>
          <tr class="danger text-danger">
            <?php if ($withDeviceEui): ?>
              <td><?= $frame['device_eui'] ?></td>
            <?php endif ?>
            <td class="text-right"><?= $i ?></td>
            <td colspan="18"></td>
          </tr>
        <?php endfor ?>
      <?php endif ?>
      <tr <?php if ($frame['isValidSolve']): ?>class="info"<?php endif ?>>
        <?php if ($withDeviceEui): ?>
          <td><?= $frame['device_eui'] ?></td>
        <?php endif ?>
        <td class="text-right"><?= $frame['count_up'] ?></td>
        <td><?= yii\helpers\Html::a($frame['latitude'], 'https://www.google.nl/maps/search/' . $frame['latitude'] . ',' . $frame['longitude'], ['target' => '_blank']) ?></td>
        <td><?= $frame['longitude'] ?></td>

        <td><?= yii\helpers\Html::a($frame['latitude_lora'], 'https://www.google.nl/maps/search/' . $frame['latitude_lora'] . ',' . $frame['longitude_lora'], ['target' => '_blank']) ?></td>
        <td><?= $frame['longitude_lora'] ?></td>
        <td class="text-right"><?= Yii::$app->formatter->asDistance($frame['location_radius_lora']) ?></td>
        <td class="text-right"><?= $frame['location_age_lora'] ?></td>
        <td class="text-right"><?= $frame['location_algorithm_lora'] ?></td>

        <td class="text-right"><?= Yii::$app->formatter->asDecimal($frame['distance'], 1) ?></td>
        <td class="text-right"><b><?= $frame['bearingArrow']  ?></b></td>

        <td class="text-right"><?= $frame['sf'] ?></td>
        <td class="text-right"><?= $frame['channel'] ?></td>
        <td class="text-right"><?= $frame['gateway_count'] ?></td>
        <?php if (count($frame['reception']) > 0): ?>
          <?php $reception = $frame['reception'][0]; ?>
          <td class="text-right"><?= $reception['lrrId'] ?></td>
          <td class="text-right"><?= Yii::$app->formatter->asDistance($reception['distance']) ?></td>
          <td class="text-right"><?= $reception['rssi'] ?></td>
          <td class="text-right"><?= $reception['snr'] ?></td>
          <td class="text-right"><?= $reception['esp'] ?></td>
        <?php else: ?>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        <?php endif ?>
        <td class="text-right"><?= $frame['time'] ?></td>
      </tr>
    <?php endforeach ?>
    </tbody>
  </table>
</div>
