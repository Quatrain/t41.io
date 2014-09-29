<?php

use Maell\ObjectModel\Collection;
use Maell\View\ListComponent;
use Maell\View\FormComponent;
use IO\Fantoir\Commune;
use Maell\View\ViewUri;

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
		
		$obj = new Commune();
		$search = new FormComponent($obj);
		$search->setDecorator('search')->register();
		
		$co = new Collection('IO\Fantoir\Commune');
		
		$list = new ListComponent($co);
		$list->setTitle("Communes")->register();
		$list->addRowAction('/io/rest/commune', "Détails", array('icon' => 'more-blue'));
	}
	
	
	public function communeAction()
	{
		$obj = Maell::_($this->_getParam('id'), 'IO\Fantoir\Commune');
		$form = new FormComponent($obj, array('display' => array('label','departement','insee','codepostal')));
		$form->setTitle("Détail commune")->setDecorator('view')->register();
		
		$list = new ListComponent($obj->getVoies(), array('columns' => array('insee','code','type','label')));
		$list->addRowAction('/io/rest/voie', "Détails",array('icon' => 'more-blue'));
		$list->setTitle("Voies")->register();
		
		ViewUri::getUriAdapter()->setArgument('id', $this->_getParam('id'));
	}
	
	
	public function voieAction()
	{
		$obj = Maell::_($this->_getParam('id'), 'IO\Fantoir\Voie');
		$form = new FormComponent($obj); //, array('display' => array('label','departement','insee','codepostal')));
		$form->setTitle("Détail voie")->setDecorator('view')->register();
	}
}
