<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

app\assets\AngularUiLeafletAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\Location */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="location-form" ng-app="locationPickerApp" ng-controller="PickerController">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

  <div class="row">
    <div class="col-sm-4">
      <h4>Type</h4>
      <?= $form->field($model, 'latitude')->textInput(['maxlength' => true]) ?>
      <?= $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-sm-8">
      <div class="well">
        <h4>Search <small><button type="button" class="btn btn-default" ng-click="do()">Pick location</button></h4>
        <leaflet id="map" event-broadcast="leaflet.events" layers="leaflet.layers" lf-center="leaflet.center" markers="leaflet.markers"></leaflet>
        <h4>Paste</h4>
        <input type="text" placeholder="Paste coordinates here&hellip;" class="form-control" ng-model="coordinates"/>
      </div>
    </div>
  </div>

  <div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
<script type="text/javascript">
  var app = angular.module('locationPickerApp', ['nemLogging', 'ui-leaflet']);

  app.controller('PickerController', ['$scope', '$timeout',
    function ($scope, $timeout) {
      var start = {
        lat: ($("#location-latitude").val()=="") ? 51.907218 : parseFloat($("#location-latitude").val()),
        lng: ($("#location-longitude").val()=="") ? 4.489303 : parseFloat($("#location-longitude").val())
      };
      
      $scope.leaflet = {
        center: {
          lat: start.lat,
          lng: start.lng,
          zoom: 12
        },
        events: {
          map: {
            enable: ['dragend', 'zoomend', 'moveend']
          }
        },
        markers: {
          point: {
            lat: start.lat,
            lng: start.lng,
            draggable: true
          }
        },
        layers: {
            baselayers: {
                osm: {
                    name: 'Kaart',
                    url: 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    type: 'xyz'
                },
                mapbox_light: {
                    name: 'Satelliet',
                    type: 'xyz',
                    url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                }
            }
        }
      };
      $scope.$on('leafletDirectiveMap.map.dragend', recenter);
      $scope.$on('leafletDirectiveMap.map.zoomend', recenter);
      $scope.$on('leafletDirectiveMap.map.moveend', recenter);

      function recenter(event, args) {
        $scope.leaflet.markers.point.lat = args.model.lfCenter.lat;
        $scope.leaflet.markers.point.lng = args.model.lfCenter.lng;
      }

      $scope.do = function () {
        $("#location-latitude").val((""+$scope.leaflet.markers.point.lat).substr(0,10));
        $("#location-longitude").val((""+$scope.leaflet.markers.point.lng).substr(0,10));
      };

      $scope.$watch('coordinates', function (value, oldValue) {
        if (value === undefined || value === '') {
          return;
        }
        $timeout(function () {
          var coords = value.split(',');
          $("#location-latitude").val(coords[0]);
          $("#location-longitude").val(coords[1]);
          $scope.coordinates = '';
        }, 120);
      });
    }
  ]);
</script>
<style>
  #map {
    width: 100%;
    height: 300px;
  }
</style>