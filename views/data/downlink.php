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
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\assets\AngularAsset;

$this->title = 'Downlink';

AngularAsset::register($this);
?>
<p>Send a downlink frame.</p>
<div ng-app="App" ng-controller="AppController" class="ng-cloak">

  <table class="table table-striped">
    <tbody>
      <tr>
        <th style="width:20%">LoRa network *</th>
        <td><select ng-options="item for item in baseUrls" ng-model="select.baseUrl" class="form-control"></select></td>
      </tr>
      <tr>
        <th style="width:20%">Device *</th>
        <td><select ng-options="device.name for device in devices" ng-model="select.device" class="form-control"></select></td>
      </tr>
      <tr class="text-muted">
          <th>Port</th>
          <td><input ng-model="select.port" class="form-control" />
              <small>Leave empty to use default port in device configuration</small></td>
      </tr>
      <tr>
        <th>Payload *</th>
        <td><input ng-model="select.payload" class="form-control" /></td>
      </tr>
      <tr>
          <th>Confirmed</th>
          <td><input type="checkbox" ng-model="select.confirmed" /></td>
      </tr>
      <tr>
        <th>Timestamp offset</th>
        <td><input ng-model="select.offset" class="form-control" />
        <small class="text-muted">Optional. If the request timestamp should be corrected for some reason.</small></td>
      <tr>
        <th></th>
        <td>
          <button ng-click="go()" class="btn btn-primary">Send downlink request</button>
          {{response}}
        </td>
      </tr>
    </tbody>
  </table>
  <div ng-show="log.length > 0">
    <h3>Log</h3>
    <table class="table table-condensed table-striped">
      <thead>
        <tr>
          <th>Time</th>
          <th>Device</th>
          <th>Payload</th>
          <th>Response</th>
        </tr>
      </thead>
      <tbody>
        <tr ng-repeat="l in log">
          <td>{{l.timestamp| date : 'HH:mm:ss'}}</td>
          <td>{{l.device_name}}</td>
          <td>{{l.payload}}</td>
          <td>{{l.response}}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<script type="text/javascript">
  var app = angular.module('App', []);

  app.controller('AppController', ['$scope', '$http', '$sce',
    function ($scope, $http, $sce) {
      $scope.devices = <?= json_encode($devices) ?>;
	  $scope.baseUrls = ['KPN Network', 'Partner network'];
      $scope.select = {
		  baseUrl: 'KPN Network',
        device: $scope.devices[0],
        payload: "00",
        offset: "0"
      };
      $scope.log = [];

      $scope.go = function () {
        var x = angular.copy($scope.select);
        x.device_name = x.device.name;
        x.device = x.device.id;
        $http({
          url: '',
          method: 'POST',
          data: {
            'select': x
          }
        }).then(function (response) {
          $scope.response = response.data;
          angular.merge(x, {timestamp: new Date(), response: angular.copy(response.data)});
          console.log(x);
          $scope.log.push(x);
        });
      };

      $scope.pl = function (payload) {
        var str = payload.substr(0, 2);
        str += "<u>";
        str += payload.substr(2, 4);
        str += "</u>";
        str += payload.substr(6);
        return $sce.trustAsHtml(str);
      }
    }
  ]);
</script>