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

var app = angular.module('dashboardApp', ['lnpSocket', 'nemLogging', 'ui-leaflet']);

app.controller('DashboardController', ['$scope', 'liveSocket', '$timeout',
  function ($scope, liveSocket, $timeout) {
    $scope.baseUrl = global.baseUrl;
    $scope.frames = [];
    $scope.connected = false;
    $scope.currentFrame = 0;
    $scope.slider = null;

    $scope.viewConfig = {
      gatewayIcon: {
        iconUrl: $scope.baseUrl + "img/gateway.png",
        iconSize: [33, 45],
        iconAnchor: [16, 45],
        popupAnchor: [1, -38]
      },
      frameIcon: {
        iconUrl: $scope.baseUrl + "img/pin-green.png",
        iconSize: [20, 34],
        iconAnchor: [10, 34]
      },
      loraLocationIcon: {
        iconUrl: $scope.baseUrl + "img/pin-red.png",
        iconSize: [20, 34],
        iconAnchor: [10, 34]
      }
    };

    $scope.leaflet = {
      center: {
        lat: 52.0674069,
        lng: 4.3496525,
        zoom: 17
      },
      defaults: {
        maxZoom: 17
      },
      bounds: {},
      markers: {}
    };

    // get cache
    if (window.localStorage["cache" + global.deviceId] !== undefined) {
      $scope.frames = JSON.parse(window.localStorage["cache" + global.deviceId]);
      angular.forEach($scope.frames, function (frame) {
        if (frame.timestamp !== undefined) {
          frame.timestamp = new Date(frame.timestamp);
        }
      });
    }

    $timeout(function () {
      var top = $("#top");
      $('.page').css({width: top.width(), height: top.height()});
      resetSlider();
      refreshMap();
    });

    liveSocket.on('connect', function () {
      $scope.connected = true;
    });

    liveSocket.on('data', function (dataIn) {
      if (dataIn.type === 'location') {
        if (dataIn.DevEUI === global.devEUI && $scope.frames.length > 0) {
          $scope.frames[0].lora = {
            latitude: dataIn.DevLAT,
            longitude: dataIn.DevLON
          };
          saveCache();
        }
        return;
      } else if (dataIn.type === "data" && dataIn.device.id !== global.deviceId) {
        return;
      }

      // store lora location
      if (dataIn.lastFrame !== undefined) {
        angular.forEach($scope.frames, function (frame) {
          if (frame.frame === undefined) {
            return;
          }

          if (frame.frame.id === dataIn.lastFrame.id) {
            angular.forEach(['latitude_lora', 'longitude_lora', 'location_age_lora', 'distance'], function (attr) {
              frame.frame[attr] = dataIn.lastFrame[attr];
              if (frame.lora === undefined) {
                frame.lora = {
                  latitude: dataIn.lastFrame.latitude_lora,
                  longitude: dataIn.lastFrame.longitude_lora
                };
              }
            });
          }
        });
        delete(dataIn.lastFrame);
      }

      dataIn.timestamp = new Date();

      // add data in to frames array
      $scope.frames.unshift(dataIn);
      if ($scope.frames.length > 30) {
        $scope.frames.pop();
      }
      saveCache();

      // reset frame view
      resetSlider();
      $(".page:eq(0)").fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
    });
    // </ when new frame in >


    $scope.remove = function (frameIndex) {
      $scope.frames.splice(frameIndex, 1);
      saveCache();
      if ($scope.currentFrame > $scope.frames.length - 1) {
        resetSlider();
      }
      refreshMap();
    };

    $scope.ago = function (date) {
      if (date === undefined || date === null) {
        return;
      }
      return date.ago();
    };

    // when another frame is selected for view
    $scope.$watch('currentFrame', function (currentFrame, previousFrame) {
      if (currentFrame === previousFrame) {
        return;
      }
      if (currentFrame > $scope.frames.length - 1) {
        return;
      }
      refreshMap();
    });

    function saveCache() {
      window.localStorage["cache" + global.deviceId] = JSON.stringify($scope.frames);
    }

    function resetSlider() {
      if ($scope.slider !== null) {
        $scope.slider.destroy();
      }
      $scope.currentFrame = 0;
      $('.content').css('transform', '');
      $scope.slider = $('.slider').pagesSliderTouch();
    }

    function refreshMap() {
      var currentFrame = $scope.frames[$scope.currentFrame];

      if (currentFrame === undefined) {
          return;
      }

      $scope.leaflet.markers = {};
      angular.forEach(currentFrame.reception, function (rec) {
        if (rec.gateway.latitude == null || rec.gateway.longitude == null) {
          return;
        }
        $scope.leaflet.markers["gateway" + rec.gateway.lrr_id] = {
          lat: parseFloat(rec.gateway.latitude),
          lng: parseFloat(rec.gateway.longitude),
          icon: $scope.viewConfig.gatewayIcon,
          message: rec.gateway.lrr_id
        };
      });

      // calculate new bounds
      if (currentFrame.frame.latitude !== null && currentFrame.frame.longitude !== null) {
          $scope.leaflet.markers.frame = {
            lat: parseFloat(currentFrame.frame.latitude),
            lng: parseFloat(currentFrame.frame.longitude),
            icon: $scope.viewConfig.frameIcon
          };
      }
      if (currentFrame.lora != null && currentFrame.lora.latitude != null && currentFrame.lora.longitude != null) {
        $scope.leaflet.markers.lora = {
          lat: parseFloat(currentFrame.lora.latitude),
          lng: parseFloat(currentFrame.lora.longitude),
          icon: $scope.viewConfig.loraLocationIcon
        };
      }

      calculateBounds($scope.leaflet.markers);
    }

    var bounds;
    function calculateBounds(list) {
      bounds = {minLat: null, minLong: null, maxLat: null, maxLong: null};
      angular.forEach(list, function (coords) {
        addCoordinateToBounds(coords);
      });
      if (bounds.minLat === null || bounds.minLong === null || bounds.maxLat === null || bounds.maxLong === null) {
        return;
      }
      $scope.leaflet.bounds = {
        northEast: {
          lat: bounds.maxLat,
          lng: bounds.maxLong
        },
        southWest: {
          lat: bounds.minLat,
          lng: bounds.minLong
        }
      };
    }

    function addCoordinateToBounds(coords) {
      if (coords === undefined || coords.lat == null || coords.lng == null) {
        return;
      }

      if (bounds.minLat === null || coords.lat < bounds.minLat) {
        bounds.minLat = coords.lat;
      }
      if (bounds.maxLat === null || coords.lat > bounds.maxLat) {
        bounds.maxLat = coords.lat;
      }
      if (bounds.minLong === null || coords.lng < bounds.minLong) {
        bounds.minLong = coords.lng;
      }
      if (bounds.maxLong === null || coords.lng > bounds.maxLong) {
        bounds.maxLong = coords.lng;
      }
    }

    $scope.safeApply = function (fn) {
      var phase = this.$root.$$phase;
      if (phase === '$apply' || phase === '$digest') {
        if (fn) {
          fn();
        }
      } else {
        this.$apply(fn);
      }
    };
  }
]);
  