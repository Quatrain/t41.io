(function(){

/* FILTERS */

angular.module('mapocFilters', []);

var distanceFilter = function() {
	return function(input) {
		var i = parseInt(input);
		var unit = 'm';

		switch (true) {
			case (i>1 && i<1000):
				unit = 'm';
				break;
			case (i>=1000):
				unit = 'Km';
				i = i/1000;
				i = i.toFixed(2);
				break; 
		}

		return i+' '+unit;
	};
};

angular.module('mapocFilters').filter('distance', distanceFilter);

}());