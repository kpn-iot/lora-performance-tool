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
/* @var $geolocStats \app\models\lora\GeolocStats */
?>
<div class="table-responsive">
  <table class="table table-condensed table-bordered table-striped">
    <thead>
      <tr>
        <th class="text-right">GW Count</th>
        <th class="text-right">Count</th>
        <th class="text-right">LocSolves</th>
        <th class="text-right">Success</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($geolocStats->perGatewayCount as $gwCount => $info): ?>
        <tr>
          <th class="text-right"><?= $gwCount ?></th>
          <td class="text-right"><?= $info['count'] ?></td>
          <td class="text-right"><?= $info['locsolves'] ?></td>
          <td class="text-right"><?= ($info['count'] === 0) ? '-' : Yii::$app->formatter->asPercent($info['locsolves'] / $info['count'], 0) ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
