(function() {

var mapController = function($scope, Nominatim, GeoJSONLayers, t41io, leafletData, leafletMarkersHelpers, geoFactory) {


    angular.extend($scope, {
        events: {
            map: {
                enable: ['zoomstart', 'drag', 'click', 'mousemove'],
                logic: 'emit'
            }
        },
        centerpoint: {
            lat: 46,
            lng: 3.5,
            zoom: 6
        },
        defaults: {
            scrollWheelZoom: false
        },
        target : 'none',
        paths: {
          data: [],
          style: geoFactory.styleShape,
          resetStyleOnMouseout: true
        },
        submit: {
          wifi: false
        },
        pathing: [],
        markers: [],
        markerData: [],
        manual_offset: 0,
        currentLayer: 'shapes',
        geoJSONLayers: new GeoJSONLayers(),
        layers: {
          baselayers: {
            mapBox: {
                  name: 'mapBox',
                  type: 'xyz',
                  url: 'http://{s}.tiles.mapbox.com/v3/rob-air.4c2da915/{z}/{x}/{y}.png'
              },
              openStreetMap: {
                name: 'openStreetMap',
                type: 'xyz',
                url: 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
            }
          },
          overlays:{
            red:    { type: 'group', name: 'E. Maternelle', visible: true },
            orange: { type: 'group', name: 'E. Elementaire', visible: true },
            blue:   { type: 'group', name: 'Detection', visible: true },
            black:  { type: 'group', name: 'Info', visible: true },
            green:  { type: 'group', name: 'BANO', visible: true },
            pink:   { type: 'group', name: 'N/C', visible: true }
          },
          data: {
            'geolocation_browser': { label: 'Geolocation: Browser', id: 'geolocation_browser', visible: true, unique: true, redraw: true, features: []},
            'geolocation_manual': { label: 'Geolocation: Manual', id: 'geolocation_manual', visible: true, unique: true, redraw: true, features: []},
            'bounding_box': { label: 'Bounding Box', id: 'bounding_box', visible: true, unique: true, redraw: true, features: []}
          }
        }
    });
    
    $scope.clear = function() {
      $scope.paths = {};
      $scope.markers = [];
      $scope.geoJSONLayers.json.features = [];
    }

    $scope.dump = function() {
      console.log($scope);
    }

    $scope.hideLayer = function(layer) {
      var layer = layer || $scope.currentLayer;

      $scope.layers.data[layer.id].visible = !$scope.layers.data[layer.id].visible;
      $scope.layers.data[layer.id].redraw = true;
      updateLayers();
    }

    $scope.addShape = function(shape) {
      //var shape = shape || $scope.arrd.selected;

      if ($scope.layers.data[shape.properties.layer] && $scope.layers.data[shape.properties.layer].unique) $scope.geoJSONLayers.removeLayer(shape.properties.layer);

      $scope.addShapes([shape]);
    }

    $scope.addShapes = function(shapes, layer) {
      var layer = layer || $scope.currentLayer;

      var features = [];

      for (var i = 0; i < shapes.length; i++) {
        // single point of interest
        if (shapes[i].geometry.type=="Point") {

          if (shapes[i].prebuilt instanceof Object) {
            //var icon = shapes[i].prebuilt.marker.icon;
            var marker = shapes[i].prebuilt.marker;
          } else {
            var marker = newMarker(shapes[i]);
          }

          $scope.markers.push(marker);

        // any type of geometry more complex than a single point
        } else {

          features.push(shapes[i]);
        }
      }


      if (features.length>=1) {
        var featureCollection = geoFactory.getFeatureCollection(features);
        pushAllFeatures(featureCollection);
      }
    }
    
    // call from outside the app scope
    // angular.element($('qt-map')).scope().boundingBox(["-13.65","49.866667","2.866667","61.5"]);
    $scope.boundingBox = function(bbox) {
    	$scope.boundingbox = bbox;
    	
        var map = this.getMap();

        map.then(function(map) {
	        var b = $scope.boundingbox;
	        map.fitBounds(L.latLngBounds([b[1], b[0]], [b[3], b[2]]));
	        //$scope.drawBox($scope.boundingbox);
        });
    };

    $scope.boundToBbox = function(bbox) {
      if (bbox) $scope.bbox = bbox;
      var map = this.getMap();
      console.log($scope.bbox);
      map.then(function(map) {
        map.fitBounds($scope.bbox);
        $scope.$apply();
      });
      
    };

    $scope.boundToCity = function() {
      var map = this.getMap();

      map.then(function(map) {
        map.panTo($scope.selectedCity.location.coordinates);
        map.setZoom(calcZoomFromPopulation($scope.selectedCity.population));

        if ($scope.selectedCity.bbox) {
          $scope.drawBox($scope.selectedCity.bbox);
        }
      });
    };

    $scope.boundToCountry = function() {
      var map = this.getMap();

      map.then(function(map) {
        if ($scope.selectedCountry.bbox) {
          var b = $scope.selectedCountry.bbox;
          map.fitBounds(L.latLngBounds([b[1], b[0]], [b[3], b[2]]));

          $scope.drawBox($scope.selectedCountry.bbox);
        }
      });
    };

    $scope.boundToRoad = function() {
      var map = this.getMap();

      map.then(function(map) {
        if ($scope.selectedRoad.bbox) {
          var b = $scope.selectedRoad.bbox;
          map.fitBounds(L.latLngBounds([b[1], b[0]], [b[3], b[2]]));

          $scope.drawBox($scope.selectedRoad.bbox);
        }
      });
    };

    $scope.saveBbox = function() {
      var map = this.getMap();

      map.then(function(map) {
        $scope.bbox = map.getBounds();
        console.log($scope.bbox);
      });
    };

    $scope.drawBox = function(b) {
      var properties = {
        layer: 'bounding_box',
        class: 'boundary',
        type: 'box',
        osm_type: 'polygon'
      };

      var coordinates = [[ [b[0], b[3]], [b[2], b[3]], [b[2], b[1]], [b[0], b[1]], [b[0], b[3]] ]];

      var s = geoFactory.getFeature('Polygon', coordinates, properties);

      $scope.addShape(s);
    };

    $scope.drawCircle = function(opt) {
      var properties = {
        class: 'geolocation',
        type: 'browser',
        display_name: opt.display_name,
        message: 'Browser geolocation result +/- accuracy',
        osm_type: 'polygon',
        radius: opt.coords.accuracy/2,
        steps: 64
      };

      var circle = geoFactory.getFeature('Circle', [opt.coords.longitude, opt.coords.latitude], properties);

      $scope.addShape(circle);
    };

    $scope.moveMarker = function(id) {
      var marker = $scope.getMarker(id);
      marker.draggable = !marker.draggable;
    };

    $scope.getMarker = function(id) {
      for (var i = 0; i < $scope.markerData.length; i++) {
        if ($scope.markerData[i].options.id && $scope.markerData[i].options.id==id) {
          return $scope.markerData[i];
        }
      };
      return false;
    };

    $scope.getMap = function() {
      return leafletData.getMap();
    };

    $scope.addTargetMarker = function() {
      $scope.manualgeolocate = true;
      var markerData = {
        lat: $scope.geolocated.coords.latitude,
        lng: $scope.geolocated.coords.longitude,
        id: 'manualpos',
        draggable: true,
        clickable: true
      };

      var marker = leafletMarkersHelpers.createMarker(markerData);

      $scope.getMap().then(function(map) {
        marker.addTo(map);
        marker.on('drag', function(e) {
          // redraw line between markers
          $scope.manual_offset = drawLine('geolocated', 'manualpos');
        });
        // marker.on('dragend', function(e) {});
      });
      
      $scope.markerData.push(marker);
    };

    $scope.getbrowserpoint = function() {
      navigator.geolocation.getCurrentPosition(function(res) {

        $scope.centerpoint = {
          lat: res.coords.latitude,
          lng: res.coords.longitude,
          zoom: calcZoomFromAccuracy(res.coords.accuracy/2)
        };

        $scope.geolocated = res;


        var msg = 'Votre position d&eacute;tect&eacute;e automatiquement';
        var markerData = {
          lat: res.coords.latitude,
          lng: res.coords.longitude,
          title: msg,
          id: 'geolocated',
          draggable: false,
          clickable: true
        };

        var marker = leafletMarkersHelpers.createMarker(markerData);
        marker.bindPopup(msg);
        //$scope.markers.push(marker);

        //var M = L.Marker(L.latlng(res.coords.latitude, res.coords.longitude));
        $scope.getMap().then(function(map) {
          marker.addTo(map);
        });
        $scope.markerData.push(marker);

        $scope.drawCircle(res);

        if (res.coords.accuracy<=150) {
          $scope.submit.wifi = true;
        }
        /*
        t41io.near({
          location: {
            lng: res.coords.longitude, 
            lat: res.coords.latitude, 
            radius: calcRadiusFromAccuracy(res.coords.accuracy/2)
          }
        }).then(function(data) {
          $scope.addShapes(t41io.formatResult(data));
        });
        */
      });
    };

    $scope.getDistance = function(from, to) {
      return (from.distanceTo(to)).toFixed(0);
    };

    $scope.submitData = function() {
      var data = {
        auto: {
          geojson: {
            type: "Feature",
            geometry: {
              type: "Point",
              coordinates: [$scope.geolocated.coords.longitude, $scope.geolocated.coords.latitude]
            },
            properties: {
              source: 'browser',
              timestamp: $scope.geolocated.timestamp,
              accuracy: $scope.geolocated.coords.accuracy,
              browser: {
                appCodeName: navigator.appCodeName,
                product: navigator.product,
                appVersion: navigator.appVersion
              }
            }
          }
        },
        manual: {
          geojson: {
            type: "Feature",
            geometry: {
              type: "Point",
              coordinates: [$scope.getMarker('manualpos')._latlng.lng, $scope.getMarker('manualpos')._latlng.lat]
            },
            properties: {
              source: 'user',
              offset: parseInt($scope.manual_offset),
              wifi: $scope.submit.wifi,
            }
          }
        }
      };
      console.log(t41io.saveMyGeoloc(data));
    };

    $scope.getCountries = function() {
      var promise = t41io.getCountries();
      promise.then(function(data){
        $scope.countries = data;
      });
    };
    //$scope.getCountries();

    $scope.getCities = function() {
      var promise = t41io.getCities();
      promise.then(function(data){
        $scope.cities = data;
      });
    };
    //$scope.getCities();

    $scope.getRoads = function() {
      var promise = t41io.getRoads();
      promise.then(function(data){
        $scope.roads = data;
      });
    };
    //$scope.getRoads();

    $scope.bano_radius = function(params) {
        if ($scope.getMarker('manualpos')===false) {
            var point = $scope.getMarker('geolocated');
            var radius = calcRadiusFromAccuracy($scope.geolocated.coords.accuracy);
        } else {
            var point = $scope.getMarker('manualpos');
            var radius = 10;
        }
        
        var query = {
            location: {
                lat: point._latlng.lat,
                lng: point._latlng.lng,
                radius: radius
            }
        };

        t41io.near(query).then(function(data){
            var shapes = t41io.formatResult(data);
            console.log(shapes);
            $scope.addShapes(shapes);
        });

    };

    function drawLine(from, to) {
      var p0 = $scope.getMarker(from);
      var p1 = $scope.getMarker(to);
      var props = {
        layer: 'geolocation_manual',
        class: 'geolocation',
        type: 'manual',
        display_name: 'Geolocation Offset',
        osm_type: 'Polygon'
      };

      var coords = [[p0._latlng.lng, p0._latlng.lat],[p1._latlng.lng, p1._latlng.lat]];

      var feature = geoFactory.getFeature('LineString', coords, props);

      $scope.addShapes([feature]);

      return geoFactory.getDistance(L.latLng(coords[0]), L.latLng(coords[1]));

    };

    function calcZoomFromAccuracy(accuracy) {
      if (accuracy<=50) return 18;
      if (accuracy<=100) return 17;
      if (accuracy<=500) return 16;
      if (accuracy<=1000) return 15;
      if (accuracy<=2500) return 14;
      if (accuracy<=5000) return 13;
      if (accuracy<=10000) return 12;
      return 11;
    };
   
    function calcRadiusFromAccuracy(accuracy) {
      if (accuracy<=50) return 2;
      if (accuracy<=100) return 4;
      if (accuracy<=500) return 10;
      if (accuracy<=1000) return 50;
      if (accuracy<=2500) return 100;
      if (accuracy<=5000) return 250;
      if (accuracy<=10000) return 500;
      return 1000;
    };

    function calcZoomFromPopulation(pop) {
      if (pop<=5000) return 16;
      if (pop<=25000) return 15;
      if (pop<=50000) return 14;
      if (pop<=100000) return 13;
      if (pop<=250000) return 15;
      if (pop<=500000) return 14;
      return 13;
    }

    function newMarker(shape) {
      var icon = geoFactory.getIcon(shape.properties.class);
      console.log(shape);
      if (shape.properties.source=='OSM') {
        var msg = '<a target="_blank" href="http://www.openstreetmap.org/'+shape.properties['@id']+'">'+shape.properties.display_name+'</a>';
      } else {
        var msg = shape.properties.message || 'No description for this feature';
      }

      return {
        properties: shape.properties,
        layer: icon.className,
        lat: shape.geojson.coordinates[1],
        lng: shape.geojson.coordinates[0],
        icon: icon,
        display_name: shape.properties.display_name,
        message: msg,
        geometry: shape.geojson
      };
    };

    function pushAllFeatures(json) {
      for (var i = 0; i < json.features.length; i++) {
        if (typeof json.features[i].properties.layer != 'undefined') {
          var layerLabel = json.features[i].properties.layer;
        } else {
          var layerLabel = json.features[i].properties.class + '_' + json.features[i].properties.type;
        }      
        if (typeof $scope.layers.data[layerLabel] != 'object') $scope.layers.data[layerLabel] = {label: layerLabel, visible: true, features: []};
        json.features[i].properties.layer = layerLabel;

        if ($scope.layers.data[layerLabel].unique) {
          $scope.layers.data[layerLabel].features = [];
        }

        $scope.layers.data[layerLabel].features.push(json.features[i]);
        $scope.layers.data[layerLabel].redraw = true;
      };

      updateLayers();
    }

    function updateLayers() {

      for (var i in $scope.layers.data) {
        if ( (!$scope.layers.data[i].visible && !$scope.layers.data[i].redraw) || $scope.layers.data[i].features.length<1 ) continue;

        $scope.geoJSONLayers.removeLayer(i);

        if (!$scope.layers.data[i].visible && $scope.layers.data[i].redraw) continue;

        $scope.geoJSONLayers.addLayer(i, $scope.layers.data[i], function(f) { return geoFactory.styleShape(f.features[0]); });
      };

      $scope.paths = $scope.geoJSONLayers.get();
    }


    $scope.eventDetected = "No events yet...";
    
    $scope.$on("leafletDirectiveMap.geojsonMouseover", function (ev, leafletEvent) {

        if (leafletEvent.target.feature.geometry.type!="Point") {
          leafletEvent.target.setStyle(
            geoFactory.styleShape(
              leafletEvent.target.feature,
              true
            )
          );
        }
    });

    $scope.$on('leafletDirectiveMap.geojsonMouseout', function(event){
        $scope.eventDetected = "geojsonMouseout";
    });

    $scope.$on('leafletDirectiveMap.geojsonClick', function(event){
        $scope.eventDetected = "geojsonClick "+$scope.paths.selected.type;
        $scope.target = $scope.paths.selected.properties.display_name;
    });

    $scope.$on('leafletDirectiveMap.zoomstart', function(event){
        $scope.eventDetected = "ZoomStart";
    });

    $scope.$on('leafletDirectiveMap.drag', function(event){
        $scope.eventDetected = "Drag";
    });

    $scope.$on('leafletDirectiveMap.click', function(event, args){
        $scope.eventDetected = "Click";
        $scope.target = args.leafletEvent.latlng.toString();
    });

    $scope.$on('leafletDirectiveMap.mousemove', function(event){
        $scope.eventDetected = "MouseMove";
    });

    $scope.$on('leafletDirectiveMarker.click', function(event, args){
        $scope.eventDetected = "Marker Click";
        $scope.target = $scope.markers[args.markerName].display_name;
    });
    
};

mapController.$inject = ['$scope', 'Nominatim', 'GeoJSONLayers', 't41io', 'leafletData', 'leafletMarkersHelpers', 'geoFactory'];

angular.module('mapocApp').controller('mapController', mapController);

}());