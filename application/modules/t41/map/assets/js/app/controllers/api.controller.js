(function() {

var ApiController = function($scope, $http, t41io) {
	angular.extend($scope, {
		apiconfig: {"countries":{"type":"restful","enabled":true,"endpoint":"v1","version":"1.0.0","label":"Query countries information","description":"Get information about any country","object":"t41\\IO\\Country","mode":"crud","methods":{"read":{"acl":{"all":""},"auth":{"parameter":"token","method":""},"apiparameters":{"code":{"type":"string","description":"ISO-3166 Country Code (see http:\/\/www.iso.org\/iso\/country_codes.htm)"},"continent":{"type":"string"},"currency":{"type":"string"},"query":{"type":"string","target":"label","searchmode":"contains"}},"properties":{"code.1":"","label":"","capital":""},"extproperties":{"population":"","continent":"","currency":""},"sortings":{"label":"ASC"},"datakey":"code.0"}}},"cities":{"type":"restful","enabled":true,"endpoint":"v1","version":"1.0.0","label":"Query cities information of a given country","description":"Provide a country code and a query of two or more letters and get the list of the existing cities in the matching country.\nGet your API token at http:\/\/t41.io\/register.","object":"t41\\IO\\City","mode":"crud","methods":{"read":{"acl":{"all":""},"apiparameters":{"country":{"type":"string","target":"country[code","constraints":{"mandatory":""}},"query":{"type":"string","target":"label","searchmode":"contains"},"code":{"type":"string","target":"code"}},"properties":{"label":"","postcode":"","county":"","source":""},"extproperties":{"fromdate":"","todate":""},"sortings":{"label":"ASC"},"datakey":"code.0"}}},"roads":{"type":"restful","enabled":true,"endpoint":"v1","version":"1.0.0","label":"Query streets of a given city","description":"","object":"t41\\IO\\Road","mode":"crud","methods":{"read":{"acl":{"all":""},"apiparameters":{"country":{"type":"string","target":"country[code"},"city":{"type":"string","target":"city[code","constraints":{"mandatory":""}},"code":{"type":"string","target":"code"},"query":{"type":"string","target":"label","searchmode":"contains"}},"properties":{"label":"","type":"","city.code.0":"","country.code.1":"","source":""},"sortings":{"label":"ASC"},"datakey":"code.0"}}},"plots":{"type":"restful","enabled":true,"endpoint":"v1","version":"1.0.0","label":"Query buildings of a given street","description":"","object":"t41\\IO\\Plot","mode":"crud","methods":{"read":{"acl":{"all":""},"apiparameters":{"country":{"type":"string","target":"country[code"},"city":{"type":"string","target":"city[code"},"road":{"type":"string","target":"road[code","constraints":{"mandatory":""}},"_road":{"type":"string","target":"road","constraints":{"mandatory":""}}},"properties":{"number":"","location":"","source":"","label":""},"extproperties":{"label":"","city":""},"sortings":{"numberNumPart":"ASC"},"datakey":"code.0"}}},"near":{"type":"restful","enabled":true,"endpoint":"v1","version":"1.0.0","label":"Query buildings of a given street","description":"","object":"t41\\IO\\Plot","mode":"crud","methods":{"read":{"acl":{"all":""},"apiparameters":{"location":{"type":"string","target":"location","searchmode":"near","constraints":{"mandatory":""}},"country":{"type":"string","target":"country[code"},"city":{"type":"string","target":"city[code"},"road":{"type":"string","target":"road[code","constraints":{"mandatory":""}}},"properties":{"number":"","source":""},"extproperties":{"label":"","location":"","city":""},"datakey":"code.0"}}},"myloc":{"type":"restful","enabled":true,"endpoint":"v1","version":"1.0.0","label":"Save user position","description":"","object":"t41\\IO\\User\\Location","mode":"crud","methods":{"create":{"acl":{"all":""},"apiparameters":{"auto":{"type":"array","constraints":{"mandatory":""}},"manual":{"type":"array","constraints":{"mandatory":""}},"ip":{"type":"string","fixedvalue":"%env:ip.address%"}}}}}},
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