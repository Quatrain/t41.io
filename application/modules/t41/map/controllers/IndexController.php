<?php

use Maell as Maell;
use Maell\ObjectModel\Collection;
use Maell\View\ListComponent;
use Maell\View\FormComponent;
use Maell\View\ViewUri;
use Maell\View;
use Maell\ObjectModel;
use Maell\Core\Api;

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
        /*
        Api::init();
        $apiconfig = Api::getConfig();
        
        View::setEnvData('apiconfig', json_encode($apiconfig));
		*/
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
    	    'app:controllers:api.controller.js',
    	    'app:services:nominatim.service.js',
    	    'app:services:t41io.service.js',
    	    'app:services:geojsonlayers.factory.js',
    	    'app:services:geofactory.factory.js'
	    ), $this->_module);
	}
	
}
