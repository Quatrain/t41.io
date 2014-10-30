(function(){
var geoFactory = function($rootScope) {
    var handler = {};
    
    handler.icons = {
        blue: {
            type: 'div',
            iconSize: [10, 10],
            className: 'blue',
            iconAnchor:  [5, 5]
        },
        red: {
            type: 'div',
            iconSize: [10, 10],
            className: 'red',
            iconAnchor:  [5, 5]
        },
        green: {
            type: 'div',
            iconSize: [10, 10],
            className: 'green',
            iconAnchor:  [5, 5]
        },
        orange: {
            type: 'div',
            iconSize: [10, 10],
            className: 'orange',
            iconAnchor:  [5, 5]
        },
        black: {
            type: 'div',
            iconSize: [10, 10],
            className: 'black',
            iconAnchor:  [5, 5]
        },
        pink: {
            type: 'div',
            iconSize: [10, 10],
            className: 'pink',
            iconAnchor:  [5, 5]
        }
    };

    handler.getIcon = function(className) {

        switch(className) {
            case 'place':
                return this.icons.pink;
            break;
            case 'amenity':
                return this.icons.red;
            break;
            case 'highway':
                return this.icons.blue;
            break;
            case 'bano':
                return this.icons.green;
            break;
            default:
                return this.icons.black;
            break;
        }
        return this.icons.pink;
    };

    handler.getFeature = function(type, coords, properties) {
      var geojson = this.getGeojson(type, coords, properties);
      var feature = new Terraformer.Feature(geojson);
      feature.properties = properties;

      return feature;
    };

    handler.getFeatureCollection = function(features) {
      return new Terraformer.FeatureCollection(features);
    };

    handler.getGeojson = function(type, coords, opt) {
      var opt = opt || {};

      switch (type) {
        case 'Point':
          return new Terraformer.Point(coords);
        break;
        case 'MultiPoint':
          return new Terraformer.MultiPoint(coords);
        break;
        case 'LineString':
          return new Terraformer.LineString(coords);
        break;
        case 'MultiLineString':
          return new Terraformer.MultiLineString(coords);
        break;
        case 'Polygon':
          return new Terraformer.Polygon(coords);
        break;
        case 'MultiPolygon':
          return new Terraformer.MultiPolygon(coords);
        break;
        case 'Circle':
          return new Terraformer.Circle(coords, opt.radius, opt.steps);
        break;
      }
    };


    handler.styleShape = function(feature, hover) {

      var hover = (hover===true)? true: false;

      var style = {
        fillColor: "yellow",
        weight: 4,
        opacity: 1,
        color: 'yellow',
        dashArray: '0',
        fillOpacity: 0.5
      };

      switch (feature.properties.class) {
        case 'highway':
              style = {
                fillColor: "#444444",
                weight: 5,
                opacity: 1,
                color: '#222222',
                dashArray: '0'
              };
          
          if (hover) {
            style.opacity = 0.7;
            style.color = '#444444';
          }
        break;
        case 'boundary':
          switch(feature.properties.type) {
            case 'administrative':
              style = {
                fillColor: "#FFFF65",
                weight: 2,
                opacity: 1,
                color: '#FFFF65',
                dashArray: '2',
                fillOpacity: 0.4
              };
              if(hover) {
                style.weight = 4;
                color = '#FFDB04';
              }
            break;
          }
        break;
        case 'amenity':
          style = {
            fillColor: "#0A4600",
            weight: 1,
            opacity: 1,
            color: 'green',
            dashArray: '0',
            fillOpacity: 0.6
          };
          if(hover===true) style.fillOpacity = 0.9;
          
          switch (feature.properties.type) {
            case "nursery":
            case "kindergarten":
              style.fillColor = '#64B058';
            break;
            case "school":
              style.fillColor = '#1E6912';
              break;
            case "post_office":
              style = {
                fillColor: "red",
                weight: 2,
                opacity: 1,
                color: 'red',
                dashArray: '0',
                fillOpacity: 0.6
              };
              break;
            case "place_of_worship":
              style = {
                fillColor: "red",
                weight: 1,
                opacity: 1,
                color: 'red',
                dashArray: '3',
                fillOpacity: 0.6
              };
            break;
          };
        break;

        case 'hull':
          style = {
            fillColor: "#58D3F7",
            fillOpacity: 0.5,
            weight: 1,
            opacity: 1,
            color: '#58D3F7',
            dashArray: '6'
          };
          if(hover) {
            style.fillOpacity = 0;
          }
        break;

        case 't41io':
          style = {
            fillColor: "#58D3F7",
            fillOpacity: 0.4,
            weight: 1,
            opacity: 1,
            color: '#000000',
            dashArray: '6'
          };

          if (hover) {
            style.fillOpacity = 0.2;
            style.dashArray = 0;
          }
      break;

      case 'bano':
        switch(feature.properties.type) {
          case 'even':
            style = {
              weight: 4,
              opacity: 1,
              color: '#0174DF'
            };
          break;
          case 'odd':
            style = {
              weight: 4,
              opacity: 1,
              color: '#4B088A'
            };
          break;
          if (hover) style.opacity = 0.6;
        }

        case 'geolocation':
          switch(feature.properties.type) {
            case 'browser':
              var style = {
                fillColor: "#58D3F7",
                fillOpacity: 0.4,
                weight: 1,
                opacity: 1,
                color: '#000000',
                dashArray: '6'
              };

              if (hover) {
                style.fillOpacity = 0.2;
                style.dashArray = 0;
              }
            break;
            case 'manual':
              var style = {
                fillColor: "#58D3F7",
                fillOpacity: 0.4,
                weight: 3,
                opacity: 1,
                color: '#041E37',
                dashArray: '0'
              };

            break;
          }
        break;
      break;
      };

      return style;
    };

    handler.getDistance = function(from, to) {
      return (from.distanceTo(to)).toFixed(0);
    };

/*
    handler.prepForBroadcast = function(msg) {
        this.message = msg;
        this.broadcastItem();
    };

    handler.broadcastItem = function() {
        $rootScope.$broadcast('handleBroadcast');
    };
*/
    return handler;
};

angular.module('mapocApp').factory('geoFactory', geoFactory);

}());
