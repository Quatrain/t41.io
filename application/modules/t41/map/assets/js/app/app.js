(function() {

	'use strict';
	
	// module definition and dependencies
	angular.module('mapocApp', ['leaflet-directive', 'ngRoute', 'mapocDirectives', 'mapocFilters']);

	// route configuration
	angular.module('mapocApp').config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
		$routeProvider
			.when('/', {
				controller: 'MapController',
				template: '<qt-map></qt-map>' //<qt-map-control></qt-map-control>
			})
			.when('/api', {
				controller: 'ApiController',
				template: '<qt-api-doc></qt-api-doc>'
			})
			.otherwise({ redirectTo: '/' });
	}]);

}());