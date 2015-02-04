<?php

use maell\View;

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
		View::setTemplate('dashboard.tpl.html');
	}
} 
