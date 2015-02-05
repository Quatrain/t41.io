(function() {

var ApiController = function($scope, $http, t41io) {
	angular.extend($scope, {
		apiconfig: {},
		baseUrl: 'http://beta.t41.io/',
		apitoken: 'fa8c9c082de9d2542b993cad0d887d29205d5bd36836a064',
		apiCall: function(e) {
			var params = [];
			for (var i in $scope.apiconfig[e].methods.read.apiparameters) {

				if (document.getElementById(e+i).value!='') params[i] = document.getElementById(e+i).value;
			}

			if (document.getElementById(e+'ext').checked) {
				params['ext'] = 1;
			}
			
			$scope.apiconfig[e].url = t41io.api + e + t41io.formatQuery(params, Object.keys(params));

			$scope.apiconfig[e].response = 'API call in progress, waiting for reponse...';

			$http.get($scope.apiconfig[e].url).success(function(data, status, headers, config) {
				$scope.successHandler(e, data);
			}).error(function(data, status, headers, config) {
				$scope.apiconfig[e].response = 'An error occured';
			});

		},
		successHandler: function(name, data) {
			if (data.status=='OK') {
				if (document.getElementById(name+'data').checked) {
					$scope.apiconfig[name].response = JSON.stringify(data.data, null, 2);
				} else {
					$scope.apiconfig[name].response = JSON.stringify(data, null, 2);
				}
			} else if (data.status=='ERR') {
				$scope.apiconfig[name].response = 'Error: '+ data.context.msg;
			}
		},
		setApiConfig: function(apiconfig) {
			$scope.apiconfig = apiconfig;
		}
	});


};

ApiController.$inject = ['$scope', '$http', 't41io'];

angular.module('mapocApp').controller('ApiController', ApiController);

}());