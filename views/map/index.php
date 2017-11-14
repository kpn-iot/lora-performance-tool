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
$this->registerCssFile('@web/css/map.css');

$this->context->layout = 'base';
$this->title = 'Map';
?>
<main ng-app='DashboardApp' ng-controller="MapController">
  <script>
        var session_id = "<?= $session_id ?>";
        var baseUrl = "<?= Url::to('@web/') ?>";
  </script>
  <div class="row">
    <div class="col-md-5 col-sm-12 col-left">
      <br />
      <div class="container-fluid">
        <a ng-href='{{baseUrl}}session/coverage/{{sessionId}}' class='btn btn-link'><span class='glyphicon glyphicon-stats'></span> Coverage report</a>
        <a ng-href='{{baseUrl}}session/geoloc/{{sessionId}}' class='btn btn-link'><span class='glyphicon glyphicon-equalizer'></span> Geoloc report</a>
        <div class='pull-right'>
          <button class="btn btn-sm btn-default" ng-click="viewConfig.showGateways = !viewConfig.showGateways">{{(viewConfig.showGateways) ? 'Hide gateways' : 'Show gateways'}}</button>
        </div>
        <h3 ng-show="data">
          {{name}}
        </h3>
        <hr />
        <input class="form-control" type="text" ng-model="gatewaySearch" placeholder="Search gateway" maxlength="8" ng-disabled="!viewConfig.showGateways"/>
        <hr />
        <p class="text-justify">
          Green dots are received frames with known GPS coordinates. White dots are corresponding LoRa location coordinates. The green line is the registered GPS track for this session. The red line is the LoRa location track for this session. For each known combination of GPS and LoRa location coordinates a black line is drawn to visualize accuracy.
        </p>
        <p class="text-justify">
          Click on a GPS dot, a LoRa location dot or a frame in the table below to highlight the corresponding frame and dots. When gateways are shown, the known (max 3) gateways that received the frame are highlighted in red.
        </p>
        <b>{{data.frames.length}} frames</b>
      </div>
      <hr />

      <div class="table-responsive">
        <table class="table table-condensed table-bordered frame-table">
          <thead>
            <tr>
              <th>FCntUp</th>
              <th>GPS</th>
              <th>GeoLoc</th>
              <th>Accuracy [m]</th>
              <th>GeoLoc<br />Relative<br />Age [s]</th>
              <th>SF</th>
              <th>Channel</th>
              <th>GW Count</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="(i, item) in data.frames" ng-click="select(i)" ng-class="{'info': (i == activeFrameId), 'danger': (i != activeFrameId && item.isMissed), 'text-danger' : (item.isMissed)}">
              <td>{{item.f_cnt_up}}</td>
              <td>
                <small ng-show="item.latitude">{{item.latitude| number : 4}}, {{item.longitude| number : 4}}</small>
              </td>
              <td>
                <small ng-show="item.latitude_lora">{{item.latitude_lora| number: 4}}, {{item.longitude_lora| number:4}}</small>
              </td>
              <td>
                <span ng-show="item.location_diff_lora">{{item.location_diff_lora|number:1}}m</span>
              </td>
              <td ng-class="{'success': (item.location_age_lora < 10 && item.location_age_lora !== null)}">
                <span ng-hide="item.location_age_lora == null">{{item.location_age_lora}}s</span>
              </td>
              <td>{{item.sf}}</td>
              <td>{{item.channel}}</td>
              <td>{{item.gateway_count}}</td>
              <td>
                <small>{{item.raw_timestamp}}</small>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="col-md-7 col-sm-12">
      <leaflet id="map" event-broadcast="leaflet.events" defaults="leaflet.defaults" lf-center="leaflet.center" bounds="leaflet.bounds" paths="leaflet.paths" markers="leaflet.markers"></leaflet>
    </div>
  </div>
</main>
