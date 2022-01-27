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

use app\assets\MapAsset;
use yii\helpers\Url;

$this->context->layout = false;
MapAsset::register($this);

if (class_exists('yii\debug\Module')) {
  $this->off(\yii\web\View::EVENT_END_BODY, [\yii\debug\Module::getInstance(), 'renderToolbar']);
}
?>
<html lang="<?= Yii::$app->language ?>">
  <head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->head() ?>
    <style>
      html, body, #map-container, #map, .angular-google-map-container, .angular-google-map {
        height: 100%;
        width: 100%;
        margin: 0;
      }
      body {
        padding-bottom: 0 !important;
      }
    </style>
  </head>
  <body>

    <?php $this->beginBody() ?>
    <div ng-app='DashboardApp' ng-controller="MapController" id="map-container">
      <script>
            var session_id = "<?= $session_id ?>";
            var baseUrl = "<?= Url::to('@web/') ?>";
            var config = {
              showMarkers: false,
              disableControl: true
            };
      </script>

      <leaflet id="map" defaults="leaflet.defaults" lf-center="leaflet.center" bounds="leaflet.bounds" paths="leaflet.paths" markers="leaflet.markers"></leaflet>
    </div>

    <?php $this->endBody() ?>
  </body>
</html>
<?php $this->endPage() ?>
