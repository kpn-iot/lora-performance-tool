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

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = false;
?>

</div>
<section class='catcher'>
  <div class='container'>
    <div class="jumbotron">
      <h1>LoRa Performance Tool</h1>
      <h3>Assess performance of the network</h3>
    </div>
  </div>
</section>

<div class="container">
  <div class="row">
    <div class='col-md-5 col-md-offset-1 col-sm-6 text-center'>
      <h4>API Endpoint</h4>
      <p class="lead"><?= Url::to(['/api/thingpark'], true) ?></p>
    </div>
    <div class='col-md-5 col-sm-6 text-center'>
      <h4>LoRa Tools</h4>
      <p class="lead"><?= Html::a('https://loratools.xoxgx.nl', 'https://loratools.xoxgx.nl', ['target' => '_blank']) ?></p>
    </div>
  </div>
  <hr />
  <div class="row">
    <div class='col-md-5 col-md-offset-1 col-sm-6'>
      <h3 class="text-center">Live measurements</h3>
      <p class="text-justify">
        To perform coverage tests and localisation performance tests with data coming from the Thingpark API, directly processed. It can be done for mobile measurements (only with Adeunis now), and static measurements). The device should be added to the device table <?= Html::a('here', ['/devices/create']) ?>. Also, in Thingpark, the URL above should be configured as endpoint (XML). When the device is turned on and the messages are received here, a new session is started. GPS and LoRa location are stored for all incoming frames and can be plotted and analysed to show the performance of the LoRa localisation.
      </p>
    </div>
    <div class='col-md-5 col-sm-6'>
      <h3 class="text-center">Quick measurements</h3>
      <p class="text-justify">
        To make reports from a CSV export from Thingpark wlogger. Usefull when your device cannot / did not send data to this portal. Upload the CSV <?= Html::a('here', ['/quick/create']) ?>, along with the name and the coordinates (optional) of the test position.
      </p>
    </div>
  </div>
  <hr/>
</div>

<script>
  $(function () {
    $('[data-toggle="popover"]').popover()
  });
</script>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-4">
      <h3 class="text-right">Activity</h3>
      <?= $this->render('_activity_graph') ?>
    </div>
    <div class="col-md-8">
      <h3><?= Html::a('Latest sessions', ['/sessions/index']) ?></h3>
      <div class="row">
        <?php foreach ($frontpageSessions as $session): ?>
          <div class="col-md-6 col-xs-12">
            <?= $this->render('/_partials/session_block', ['session' => $session]) ?>
          </div>
        <?php endforeach ?>
      </div>
    </div>
  </div>
</div>
