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

/* @var $frameCollection app\models\lora\FrameCollection */

$this->registerCss("@media print {
  a[href]:after { content: none; }
}");

$frames = $frameCollection->frames;
$withDeviceEui = ($frameCollection->nrDevices > 1);
$gatewayColors = $frameCollection->coverage->gwColors;
?>
<div class="table-responsive">
  <table class="table table-condensed table-bordered">
    <thead>
      <tr>
        <?php if ($withDeviceEui): ?>
          <th>DevEUI</th>
        <?php endif ?>
        <th>FCntUp</th>
        <th>Time</th>        
        <th>Latitude</th>
        <th>Longitude</th>
        <th class="text-right">SF</th>
        <th class="text-right">Channel</th>
        <th class="text-right">GW Count</th>
        <th class="text-right">Gateway ID</th>
        <th class="text-right">Distance to GW [m]</th>
        <th class="text-right">RSSI</th>
        <th class="text-right">SNR</th>
        <th class="text-right">ESP</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($frames as $index => $frame): ?>
        <?php if ($frameCollection->nrDevices == 1 && $index > 0 && ($frame['count_up'] != $frames[$index - 1]['count_up'] + 1)): ?>
          <?php for ($i = $frames[$index - 1]['count_up'] + 1; $i < $frame['count_up']; $i++): ?>
            <tr class="danger text-danger">
              <?php if ($withDeviceEui): ?>
                <td><?= $frame['device_eui'] ?></td>
              <?php endif ?>
              <td colspan="12"><?= $i ?></td>
            </tr>
          <?php endfor ?>
        <?php endif ?>

        <?php
        $rowspan = "rowspan='" . count($frame['reception']) . "'";
        ?>
        <tr>
          <?php if ($withDeviceEui): ?>
            <td <?= $rowspan ?>><?= $frame['device_eui'] ?></td>
          <?php endif ?>
          <td <?= $rowspan ?>><?= $frame['count_up'] ?></td>
          <td <?= $rowspan ?>><?= $frame['time'] ?></td>
          <td <?= $rowspan ?>><?= yii\helpers\Html::a($frame['latitude'], 'https://www.google.nl/maps/search/' . $frame['latitude'] . ',' . $frame['longitude'], ['target' => '_blank']) ?></td>
          <td <?= $rowspan ?>><?= $frame['longitude'] ?></td>

          <td <?= $rowspan ?> class="text-right"><?= $frame['sf'] ?></td>
          <td <?= $rowspan ?> class="text-right"><?= $frame['channel'] ?></td>
          <td <?= $rowspan ?> class="text-right"><?= $frame['gateway_count'] ?></td>
          <?php
          for ($i = 0; $i < count($frame['reception']); $i++):
            $reception = $frame['reception'][$i];
            ?>
            <td class="text-right" style="background-color:<?= $gatewayColors[$reception['lrrId']] ?>;color:#fff"><?= $reception['lrrId'] ?></td>
            <td class="text-right"><?= $reception['distance'] ?></td>
            <td class="text-right"><?= $reception['rssi'] ?></td>
            <td class="text-right"><?= $reception['snr'] ?></td>
            <td class="text-right"><?= $reception['esp'] ?></td>
            <?php if ($i < count($frame['reception']) - 1): ?>
            </tr><tr>
            <?php endif ?>
          <?php endfor ?>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
