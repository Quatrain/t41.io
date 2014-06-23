<?php

use Maell\ObjectModel\Collection;
use Maell\View\ListComponent;
use Maell\View\FormComponent;

require 'application/controllers/LoggedController.php';

/**
 * RestController
 * 
 * @author
 * @version 
 */
class IO_RestController extends LoggedController {

	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		$obj = Maell::_('IO\Fantoir\Voie');
		$search = new FormComponent($obj);
		$search->setDecorator('search')->register();
		
		$co = new Collection('IO\Fantoir\Voie');
		
		$list = new ListComponent($co);
		$list->setTitle("Extrait Mongo")->register();
	}
}
