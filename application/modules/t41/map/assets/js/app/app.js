(function() {

	'use strict';
	
	// module definition and dependencies
	angular.module('mapocApp', ['leaflet-directive', 'ngRoute', 'mapocDirectives', 'mapocFilters']);

	// route configuration
	angular.module('mapocApp').config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
		$routeProvider
			.when('/', {
				controller: 'mapController',
				//templateUrl: '/maell/app/t41/map/app:views:dashboard.tmpl.html'
				template: '<qt-map></qt-map>' //<qt-map-control></qt-map-control>
			})
			.when('/api', {
				controller: 'apiController',
				template: '<qt-api-doc></qt-api-doc>'
			})
			.otherwise({ redirectTo: '/' });
	}]);

}());