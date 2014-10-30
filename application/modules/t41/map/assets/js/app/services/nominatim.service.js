(function() {

	var Nominatim = function($http, $q) {
	  this.search = function(query) {
	    var d = $q.defer();
	    var query = query || 'rue paradis';
	    var url = 'http://nominatim.openstreetmap.org/search?format=json&polygon_geojson=1&dedupe=0';
	    url += '&viewbox=4,44,6,42&bounded=1&city=Marseille&country=France';

	    if (query.search("[0-9]{5}$")!=-1) {
	      var cp = query.slice(query.search("[0-9]{5}$"));
	      url += '&postalcode='+cp;
	    }

	    url += '&street='+encodeURIComponent(query);

	    $http.get(url).success(function(response) {
	      d.resolve(response);
	    }).error(function(error) {
	      d.reject(error);
	    });

	    return d.promise;
	  };

	  this.translate = function(geojson) {
	    var r = {
	      type: geojson.type,
	      latlngs: [],
	      style: {}
	    };

	    for (var i = 0; i < geojson.coordinates.length; i++) {
	      r.latlngs.push({lat: geojson.coordinates[i][1], lng: geojson.coordinates[i][0]});
	    };

	    return r;
	  };
	};


	Nominatim.$inject = ['$http', '$q'];
	angular.module('mapocApp').service('Nominatim', Nominatim);
	
}());