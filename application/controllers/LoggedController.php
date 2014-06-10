<?php

use	Maell\View;
use	Maell\Core\Layout;


/**
 * IndexController
 *
 * @author
 * @version
 */
require_once 'vendor/maellio/maell/controllers/CrudController.php';

class LoggedController extends Maell_CrudController {


	public function init()
	{
		parent::init();
		View::addRequiredLib('jquery/jquery.min', 'js');
		View::addCoreLib('core.js');
		View::addCoreLib('object.js');

		View::addCoreLib('locale.js');
		View::addEvent("maell.locale.lang='en'", 'js');

		View::addCoreLib('view.js');
		View::addCoreLib('view:alert.js');
		View::addCoreLib('view:table.js');
		View::addCoreLib('view:grid.js');
		View::addCoreLib('view:form.js');

		// get default menu
		$defaultMenu = Layout::getMenu('main');
		
		// inject in a view object
		$menu = new View\MenuComponent();
		$menu->setMenu($defaultMenu);
		//$menu->setRole($this->_session->role);

		$menu->getMenu();
		$menu->register('menu');
	}
}
