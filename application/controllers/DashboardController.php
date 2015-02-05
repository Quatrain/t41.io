<?php

use maell\View;
use t41\Wa\Address;
use Maell\View\FormComponent;
use Maell\Backend;
use Maell\Core\Api;

/**
 * IndexController
 * 
 * @author
 * @version 
 */

require_once 'LoggedController.php';

class DashboardController extends LoggedController {

	
	public function preDispatch() {}
	
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction()
	{
		$this->_addMapLibs();
		$this->_addForm();

		Api::init();
        $apiconfig = Api::getConfig();
        
        View::addEvent("angular.element($('qt-api-doc')).scope().setApiConfig(".json_encode($apiconfig).");", 'js');

		View::setTemplate('dashboard.tpl.html');
	}

	private function _addForm()
	{
		View::addVendorLib('components/jquery/jquery.min.js');
		View::addModuleLib('address.js', 'app/t41/wa');
		View::addEvent('address = new wa.address(); address.init()', 'js');
		
		$address = new Address();
		
		$form = new FormComponent($address, ['display' => ['plot','road','postcode','city','country']]);
		$form->setTitle("Essayez l'API");

		$form->getElement('city')->setDecorator('autocomplete')->setDecoratorParams(array('retprops' => 'label,postcode'));
		
		$form->getElement('country')->getCollection()->setParameter('altkey', 'code.1');
		
		$form->register('form');
	}

	private function _addMapLibs()
	{
	    View::addModuleLib(array(
	    	'leaflet.css',
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
	    ), 'app/t41/map');
	}
} 
