(function(){

/* DIRECTIVES */

angular.module('mapocDirectives', []);


var qtMap = function() {
	return {
		restrict: 'E',
		//templateUrl: 'app/views/map.tmpl.html'
		template: '<leaflet center="centerpoint" markers="markers" geojson="paths" paths="pathing" layers="layers" event-broadcast="events" defaults="defaults"></leaflet>'
	};
};
angular.module('mapocDirectives').directive('qtMap', qtMap);



var qtMapControl = function() {
	return {
		restrict: 'E',
		//templateUrl: 'app/views/control.tmpl.html'
		template: '<div id="control" class="leaflet-control-layers"><h1 style="margin: 0;">Controls</h1><div qt-bound-control></div><div qt-layers-control></div><div qt-actions-control></div><div qt-info-control></div></div>'
	}
};
angular.module('mapocDirectives').directive('qtMapControl', qtMapControl);



var qtLayersControl = function() {
	return {
		restrict: 'A',
		//templateUrl: 'app/views/layerscontrol.tmpl.html'
		template: '<hr /><ul id="layers">Layers<li ng-repeat="layer in layers.data" ng-class="{active: layer.visible}" ng-click="hideLayer(layer.label)">{{layer.label}} <span ng-hide="layer.unique">({{layer.features.length}})</span> <span ng-show="layer.unique">(U:{{layer.features.length}})</span></li></ul>'
	};
};
angular.module('mapocDirectives').directive('qtLayersControl', qtLayersControl);



var qtActionsControl = function() {
	return {
		restrict: 'A',
		//templateUrl: 'app/views/actionscontrol.tmpl.html'
		template: '<button ng-click="getbrowserpoint()">Auto. geoloc</button><button ng-click="addTargetMarker()" ng-show="geolocated">Manual geoloc</button><button ng-click="bano_radius()" ng-show="geolocated">BANO search</button><button ng-click="submitData()" ng-show="manualgeolocate">Submit</button>'
	};
};
angular.module('mapocDirectives').directive('qtActionsControl', qtActionsControl);



var qtBoundControl = function() {
	return {
		restrict: 'A',
		//templateUrl: 'app/views/boundcontrol.tmpl.html'
		template: '<label>Latitude</label> <input type="number" step="0.005" ng-model="centerpoint.lat"><br /><label>Longitude</label> <input type="number" step="0.005" ng-model="centerpoint.lng"><br /><label>Zoom</label> <input type="number" step="1" ng-model="centerpoint.zoom"><br />    <hr /><div>Event: <strong ng-bind="eventDetected"></strong></div><div>Target: <strong>{{target}}</strong></div>'
	}
};
angular.module('mapocDirectives').directive('qtBoundControl', qtBoundControl);



var qtInfoControl = function() {
	return {
		restrict: 'A',
		//templateUrl: 'app/views/infocontrol.tmpl.html'
		template: '<div class="info" ng-show="geolocated"><hr /><div><input type="checkbox" ng-model="submit.wifi" id="subwifi"><label for="subwifi">Wifi ON</label></div><div>Accuracy: {{ geolocated.coords.accuracy | distance }}</div><div>Offset: {{ manual_offset | distance }}</div></div>'
	};
};
angular.module('mapocDirectives').directive('qtInfoControl', qtInfoControl);



var qtApiDoc = function() {
	return {
		restrict: 'E',
		template: '<h2>Documentation</h2><div ng-repeat="(key, api) in apiconfig"><h3>/{{api.endpoint}}/{{key}}</h3><span>{{api.description}}</span></div>'
	};
};
angular.module('mapocDirectives').directive('qtApiDoc', qtApiDoc);



}());