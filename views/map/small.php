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

MapAsset::register($this);
?>
<style>
  #map-container, #map, .angular-google-map-container, .angular-google-map {
    height: 100%;
  }
  body {
    padding-bottom: 0 !important;
  }
</style>
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
