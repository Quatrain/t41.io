<?php

use maell\View;
use t41\Wa\Address;
use Maell\View\FormComponent;
use Maell\Backend;
use Maell\Core\Api;

require 'application/controllers/LoggedController.php';

/**
 * Documentation ApiController
 * 
 * @author
 * @version 
 */
class Doc_ApiController extends LoggedController {

	
	public function indexAction()
	{
	    $this->_addAppLibs();
		View::setTemplate('api.tpl.html');
	}

	protected function _addAppLibs()
	{
		View::addModuleLib(array(
			'leaflet.css',
			'app.css',
			'leaflet.min.js',
			'terraformer.min.js',
			'angular.min.js',
			'angular.min.js.map',
			'angular-route.min.js',
			'angular-route.min.js.map',
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
		), 'app/t41/map');
	}
}
