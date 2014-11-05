(function(){
  var t41io = function($http, $q, geoFactory) {
    this.api = 'http://beta.t41.io/api/v1/';
    this.token = 'fa8c9c082de9d2542b993cad0d887d29205d5bd36836a064';

    this.getCountries = function() {
      var d = $q.defer();
      $http.get('app/data/country.json').success(function(response) {
        d.resolve(response);
      }).error(function(error) {
        d.reject(error);
      });
      return d.promise;
    };

    this.getCities = function() {
      var d = $q.defer();
      $http.get('app/data/city.json').success(function(response) {
        d.resolve(response);
      }).error(function(error) {
        d.reject(error);
      });
      return d.promise;
    };

    this.getRoads = function() {
      var d = $q.defer();
      $http.get('app/data/avignon.json').success(function(response) {
        d.resolve(response);
      }).error(function(error) {
        d.reject(error);
      });
      return d.promise;
    };

    this.countries = function(query) {
      var query = query || {};
      var params = ['code','currency','continent','query'];

      this.get(
        'countries', 
        this.formatQuery(query, params)
      ).then(function(data) {
        return this.formatResult(data);
      });
    };

    this.cities = function(query) {
      var query = query || {};
      var params = ['country','query'];

      this.get(
        'cities', 
        this.formatQuery(query, params)
      ).then(function(data) {
        return this.formatResult(data);
      });
    };

    this.roads = function(query) {
      var query = query || {};
      var params = ['country','city','query'];

      this.get(
        'roads', 
        this.formatQuery(query, params)
      ).then(function(data) {
        return this.formatResult(data);
      });
    };

    this.plots = function(query) {
      var query = query || {};
      var params = ['country','city','road'];
      
      this.get(
        'plots', 
        this.formatQuery(query, params)
      ).then(function(data) {
        return this.formatResult(data);
      });
    };

    this.near = function(query) {
      var query = query || {};
      var params = ['location'];
      var radius = query.location.radius || 5;

      return this.get(
        'near', 
        '?token='+this.token+'&location='+query.location.lat+','+query.location.lng+',,'+radius
      );
    };

    this.saveMyGeoloc = function(data) {
      var data = data || {};

      return this.post('myloc', data);
    };

    this.get = function(target, query) {
      var d = $q.defer();
      var url = this.api + target + query;
      console.log(url);

      $http.get(url).success(function(response) {
        if (response.status=='OK') { 
          d.resolve(response.data);
        } else {
          console.log(response.status, target, query);
        }
      }).error(function(error) {
        d.reject(error);
      });

      return d.promise;
    };

    this.post = function(target, data) {
      var d = $q.defer();
      var url = this.api + target;

      $http.post(url, data).success(function(response) {
        if (response.status=='OK') {
          d.resolve(response.data);
        } else {
          console.log(response.status, target, data);
        }
      }).error(function(error) {
        d.reject(error);
      });

      return d.promise;
    };

    // formater le résultat de la requête
    this.formatResult = function(data) {
      var result = [];

      for (var f in data) {

        var icon = geoFactory.getIcon('black');

        var msg = '<strong>'+data[f].label+'<br/>['+f+']<br/><em>Source: '+data[f].source+'</em>';
        var marker = {};

        marker = {
          properties: data[f],
          layer: icon.className,
          lat: data[f].location.coordinates[1],
          lng: data[f].location.coordinates[0],
          icon: icon,
          display_name: data[f].label,
          message: msg,
          geometry: data[f].location
        };

        result.push({prebuilt:{marker:marker}, geometry:data[f].location});
      };

      return result;
    };

    // formater les parametres à ajouter à l'url
    this.formatQuery = function(query, params) {
      var result = '?token='+this.token;
      for (var i = 0; i < query.length; i++) {
        if(params.indexOf(i)!='-1') result += '&'+i+'='+query[i];
      };
      return result;
    };
  };


  t41io.$inject = ['$http', '$q', 'geoFactory'];
  angular.module('mapocApp').service('t41io', t41io);

}());