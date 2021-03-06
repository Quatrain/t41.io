<?php

use maell\View;
use t41\Wa\Address;
use t41\IO\Apiaccess;
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
		$this->_addAppLibs();
		$this->_addForm();

		$apiaccess = new Apiaccess();
		$form = new FormComponent($apiaccess, ['display' => ['nom','societe','email']]);
		$form->setTitle('DEMANDE D\'ACCÈS À L\'API')->register('accessform');
		
		View::setTemplate('dashboard.tpl.html');
	}

	protected function _addForm()
	{
		View::addModuleLib('address.js', 'app/t41/wa');
		View::addEvent('address = new wa.address(); address.init()', 'js');
		
		$address = new Address();
		
		$form = new FormComponent($address, ['display' => ['plot','road','postcode','city','country']]);
		$form->setTitle("Essayez l'API");

		$form->getElement('city')->setDecorator('autocomplete')->setDecoratorParams(array('retprops' => 'label,postcode,bbox'));
		$form->getElement('road')->setDecorator('autocomplete')->setDecoratorParams(array('retprops' => 'type,label,bbox'));
		
		$form->getElement('country')->getCollection()->setParameter('altkey', 'code.1');
		$form->getElement('country')->setDecorator('autocomplete')->setDecoratorParams(array('retprops' => 'label,bbox'));
		
		$form->register('apitryform');
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
