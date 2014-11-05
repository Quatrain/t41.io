(function() {

var apiController = function($scope) {
	angular.extend($scope, {
		apiconfig: window.apiconfig
	});
};

angular.module('mapocApp').controller('apiController', apiController);

}());