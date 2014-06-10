<?php

use Maell\View;

/**
 * IndexController
 * 
 * @author
 * @version 
 */
class IndexController extends \Zend_Controller_Action {

	
	public function init()
	{
		View::setTemplate('default.html');
	}
	
	public function indexAction()
	{
		$this->_redirect('/dashboard');
	}
}
