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

app.controller('MapController', ['$scope', '$http', 'leafletData',
  function ($scope, $http, leafletData) {
    $scope.baseUrl = baseUrl;
    $scope.sessionId = session_id;

    /** CONFIG **/
    $scope.config = angular.extend({
      showMarkers: true,
      disableControl: false
    }, (typeof config !== 'object') ? {} : config);

    // icons: http://kml4earth.appspot.com/icons.html#paddle
    $scope.viewConfig = {
      showGateways: false,
      gatewayIconActive: {
        iconUrl: $scope.baseUrl + "img/gateway_active.png",
        iconSize: [33, 45],
        iconAnchor: [16, 45],
        popupAnchor: [1, -38]
      },
      gatewayIcon: {
        iconUrl: $scope.baseUrl + "img/gateway.png",
        iconSize: [33, 45],
        iconAnchor: [16, 45],
        popupAnchor: [1, -38]
      },
      frameIcon: {
        iconUrl: $scope.baseUrl + "img/grn-circle-lv.png",
        iconSize: [8, 8],
        iconAnchor: [4, 4]
      },
      frameIconMissing: {
        iconUrl: $scope.baseUrl + "img/red-circle-lv.png",
        iconSize: [8, 8],
        iconAnchor: [4, 4]
      },
      frameIconActive: {
        iconUrl: $scope.baseUrl + "img/grn-circle-lv.png",
        iconSize: [12, 12],
        iconAnchor: [6, 6]
      },
      loraLocationIcon: {
        iconUrl: $scope.baseUrl + "img/wht-diamond-lv.png",
        iconSize: [8, 8],
        iconAnchor: [4, 4]
      },
      loraLocationIconActive: {
        iconUrl: $scope.baseUrl + "img/blu-diamond-lv.png",
        iconSize: [12, 12],
        iconAnchor: [6, 6]
      },
      staticIcon: {
        iconUrl: $scope.baseUrl + "img/pin-red.png",
        iconSize: [20, 34],
        iconAnchor: [10, 34]
      },
      gpsTrackStroke: {color: 'rgba(0,150,0,0.6)', weight: 3},
      loraTrackStroke: {color: 'rgba(255,0,0,0.6)', weight: 2}
    };

    $scope.leaflet = {
      center: {
        lat: 51.968256,
        lng: 4.362128,
        zoom: 12
      },
      tiles: {
        url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        // url: "https://{s}.tile.openstreetmap.se/hydda/base/{z}/{x}/{y}.png"
      },
      defaults: {
        maxZoom: 17
      },
      events: {
        markers: {
          enable: ['click']
        }
      },
      bounds: {},
      paths: {},
      markers: {}
    };

    if ($scope.config.disableControl === true) {
      $scope.leaflet.defaults.keyboard = false;
      $scope.leaflet.defaults.dragging = false;
      $scope.leaflet.defaults.zoomControl = false;
      $scope.leaflet.defaults.doubleClickZoom = false;
      $scope.leaflet.defaults.scrollWheelZoom = false;
    }

    $scope.$on('leafletDirectiveMarker.map.click', function (event, args) {
      if (args.modelName.indexOf('frame') === 0) {
        var id = args.modelName.replace('frame', '');
        $scope.select(id);
      } else if (args.modelName.indexOf('lora') === 0) {
        var id = args.modelName.replace('lora', '');
        $scope.select(id);
      }
    });

    /** DATA **/
    $scope.data = {
      frames: [],
      gateways: []
    };

    $http({
      url: $scope.baseUrl + 'map/gateways'
    }).then(function (response) {
      $scope.data.gateways = [];
      angular.forEach(response.data, function (record, index) {
        record.latitude = parseFloat(record.latitude);
        record.longitude = parseFloat(record.longitude);
        if (!isNaN(record.latitude) && !isNaN(record.longitude)) {
          $scope.data.gateways.push(record);
        }
      });
    });

    if ($scope.sessionId != '') {
      leafletData.getMap('map').then(function () {
        $http({
          url: $scope.baseUrl + 'map/frames?session_id=' + $scope.sessionId
        }).then(function (response) {
          $scope.data.frames = response.data.frames;
          $scope.data.staticPointers = response.data.staticPointers;
          $scope.name = response.data.name;
          drawMap();
        });
      });
    }


    $scope.$watch('viewConfig.showGateways', function (value, oldValue) {
      if (value === oldValue) {
        return;
      }

      if (!value) {
        $scope.gatewaySearch = '';
        angular.forEach($scope.leaflet.markers, function (marker, index) {
          if (marker.type === 'gateway') {
            delete($scope.leaflet.markers[index]);
          }
        });
        return;
      }

      angular.forEach($scope.data.gateways, function (point, pointI) {
        $scope.leaflet.markers['gateway' + pointI] = {
          type: 'gateway',
          name: point.name,
          message: point.name + ((point.type == null) ? '' : " | Type: " + point.type),
          lat: point.latitude,
          lng: point.longitude,
          icon: $scope.viewConfig.gatewayIcon
        };
      });
    });


    /** INTERACTION **/

    $scope.activeFrameId = null;
    $scope.select = function (frameI) {
      var deselect = ($scope.activeFrameId === frameI);
      var foundLoRaMarker = false;

      angular.forEach($scope.leaflet.markers, function (marker, markerId) {
        if (markerId === "frame" + $scope.activeFrameId) { //unhighlight current selected marker
          marker.icon = marker.baseIcon;
        } else if (!deselect && markerId === "frame" + frameI) { //highlight gps loc
          marker.icon = $scope.viewConfig.frameIconActive;
        } else if (markerId === "lora" + $scope.activeFrameId) { //unhighlight current selected lora marker
          marker.icon = $scope.viewConfig.loraLocationIcon;
        } else if (!deselect && markerId === "lora" + frameI) { //highlight lora geoloc
		  marker.icon = $scope.viewConfig.loraLocationIconActive;
          if (marker.radius !== null) {
            $scope.leaflet.paths.circle = {
              type: "circle",
              fill: true,
              fillOpacity: 0.05,
              weight: 1,
              radius: marker.radius,
              latlngs: {
                lat: marker.lat,
                lng: marker.lng
              }
            };
            foundLoRaMarker = true;
          }
        } else if (marker.type === "gateway") { //highlight receiving gateways
          if (marker.icon !== $scope.viewConfig.gatewayIcon) {
            marker.icon = $scope.viewConfig.gatewayIcon;
          } else if (!deselect && $scope.data.frames[frameI].reception.indexOf(marker.name) > -1) {
            marker.icon = $scope.viewConfig.gatewayIconActive;
          }
        }
      });

      if (!foundLoRaMarker || deselect) {
        delete($scope.leaflet.paths.circle);
      }

      $scope.activeFrameId = (deselect) ? null : frameI;
    };

    $scope.$watch('gatewaySearch', function (value, oldValue) {
      if (value === oldValue || (value.length !== 0 && value.length !== 8)) {
        return;
      }

      $scope.gwFound = false;
      angular.forEach($scope.leaflet.markers, function (marker) {
        if (marker.type !== 'gateway') {
          return;
        }

        if (marker.name === value) {
          if (marker.icon.iconUrl !== $scope.viewConfig.gatewayIconActive.iconUrl) {
            marker.icon = $scope.viewConfig.gatewayIconActive;
            $scope.gwFound = marker;
          }
        } else if (marker.icon.iconUrl !== $scope.viewConfig.gatewayIcon.iconUrl) {
          marker.icon = $scope.viewConfig.gatewayIcon;
        }
      });

      if ($scope.gwFound !== false) {
        leafletData.getMap('map').then(function (map) {
          map.panTo(new L.LatLng($scope.gwFound.lat, $scope.gwFound.lng));
        });
      }
    });

    /** MAP OBJECTS **/

    function calculateBounds(coords) {
      if ($scope.bounds.minLat === null || coords.lat < $scope.bounds.minLat) {
        $scope.bounds.minLat = coords.lat;
      }
      if ($scope.bounds.maxLat === null || coords.lat > $scope.bounds.maxLat) {
        $scope.bounds.maxLat = coords.lat;
      }
      if ($scope.bounds.minLong === null || coords.lng < $scope.bounds.minLong) {
        $scope.bounds.minLong = coords.lng;
      }
      if ($scope.bounds.maxLong === null || coords.lng > $scope.bounds.maxLong) {
        $scope.bounds.maxLong = coords.lng;
      }
    }

    function drawMap() {
      $scope.bounds = {minLat: null, minLong: null, maxLat: null, maxLong: null};
      $scope.leaflet.paths = {};
      $scope.leaflet.markers = {};

      var gpsLatlngs = [];
      var loraLatlngs = [];


      angular.forEach($scope.data.staticPointers, function (pointer) {
        $scope.leaflet.markers["static" + pointer.sessionId] = {
          lat: parseFloat(pointer.latitude),
          lng: parseFloat(pointer.longitude),
          icon: $scope.viewConfig.staticIcon
        };
        calculateBounds($scope.leaflet.markers["static" + pointer.sessionId]);
      });

      if ($scope.data.frames === undefined) {
        return;
      }

      angular.forEach($scope.data.frames, function (frame, frameI) {
        if (frame.latitude != null) {
          var icon = (frame.isMissed) ? $scope.viewConfig.frameIconMissing : $scope.viewConfig.frameIcon;
          var frameCoordinates = {
            lat: parseFloat(frame.latitude),
            lng: parseFloat(frame.longitude)
          };
          calculateBounds(frameCoordinates);

          if ($scope.config.showMarkers) {
            $scope.leaflet.markers["frame" + frameI] = {
              lat: frameCoordinates.lat,
              lng: frameCoordinates.lng,
              baseIcon: icon,
              icon: icon
            };
          }

          gpsLatlngs.push(frameCoordinates);
        }

        if (frame.latitude_lora != null && frame.location_age_lora < 10) {
          var loraCoordinates = {
            lat: parseFloat(frame.latitude_lora),
            lng: parseFloat(frame.longitude_lora),
            radius: (frame.location_radius_lora === null) ? null : parseFloat(frame.location_radius_lora)
          };
          calculateBounds(loraCoordinates);

          if (frame.latitude != null) {
            $scope.leaflet.paths["diff" + frameI] = {
              latlngs: [frameCoordinates, loraCoordinates],
              weight: 1,
              color: "#000"
            };
          }
          loraLatlngs.push(loraCoordinates);

          if ($scope.config.showMarkers) {
            $scope.leaflet.markers["lora" + frameI] = {
              lat: loraCoordinates.lat,
              lng: loraCoordinates.lng,
              radius: loraCoordinates.radius,
              icon: $scope.viewConfig.loraLocationIcon
            };
          }
        }
      });

      $scope.leaflet.paths.gps = angular.extend({
        latlngs: gpsLatlngs
      }, $scope.viewConfig.gpsTrackStroke);

      $scope.leaflet.paths.lora = angular.extend({
        latlngs: loraLatlngs
      }, $scope.viewConfig.loraTrackStroke);


      if ($scope.bounds.minLat !== null && $scope.bounds.minLong !== null) {
        $scope.leaflet.bounds = {
          northEast: {
            lat: $scope.bounds.maxLat,
            lng: $scope.bounds.maxLong
          },
          southWest: {
            lat: $scope.bounds.minLat,
            lng: $scope.bounds.minLong
          }
        };
      }
    }
  }
]);
