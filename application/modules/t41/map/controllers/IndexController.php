<?php

use Maell\ObjectModel\Collection;
use Maell\View\ListComponent;
use Maell\View\FormComponent;
use Maell\View\ViewUri;
use maell\View;

require 'application/controllers/LoggedController.php';

/**
 * RestController
 * 
 * @author
 * @version 
 */
class map_IndexController extends LoggedController {

	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		//View::setTemplate('map_full.html');
		View::setTemplate('dashboard.tpl.html');
        $this->_addLibs();
		
	}
	
	private function _addLibs() {
	    View::addModuleLib(
    	    array('leaflet.css',
    	    'app.css',
    	    'leaflet.min.js',
    	    'terraformer.min.js',
    	    'angular.min.js',
    	    'angular-route.min.js',
    	    'app:app.js',
    	    'app:directives:leaflet.min.js',
    	    'app:directives:app.filters.js',
    	    'app:directives:app.directives.js',
    	    'app:controllers:map.controller.js',
    	    'app:services:nominatim.service.js',
    	    'app:services:t41io.service.js',
    	    'app:services:geojsonlayers.factory.js',
    	    'app:services:geofactory.factory.js'
	    ), $this->_module);
	}
	
}
