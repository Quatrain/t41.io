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

require 'application/controllers/DashboardController.php';

class IO_DocController extends DashboardController {

	
	public function preDispatch() {}
	
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction()
	{
		$this->_addAppLibs();

		Api::init();
		$apiconfig = Api::getConfig();

		View::addEvent("angular.element($('qt-api-doc')).scope().setApiConfig(".json_encode($apiconfig).");", 'js');

		View::setTemplate('api.tpl.html');
	}

}
