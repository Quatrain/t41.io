<?php

use t41\Wa\Address;
use Maell\View\FormComponent;
use Maell\View;
use Maell\ObjectModel\Collection;
use Maell\Backend;

require_once 'vendor/maellio/maell/controllers/DefaultController.php';


class Wa_AddressController extends Maell_DefaultController {


	public function createAction()
	{
/*   		$country = new Collection('t41\IO\Country');
  		$country->setParameter('altkey', 'code.1');
		$country->having('label')->contains('fr');
		//$country->having('country')->equals('407a61ffffffffffffffffff');
		$country->debug();
		Zend_Debug::dump($country->getMembers());
		die; */
		
		View::addVendorLib('components/jquery/jquery.min.js');
		View::addModuleLib('address.js', $this->_module);
		View::addEvent('address = new wa.address(); address.init()', 'js');
		
		$address = new Address();
		
		$form = new FormComponent($address, ['display' => ['plot','road','city','country']]);
		$form->setTitle("New Address");
		
		$form->getElement('country')->getCollection()->setParameter('altkey', 'code.1');
		//Zend_Debug::dump($form->getElement('country')->getCollection()->getParameters()); die;
		
		
		$form->register();
	}
}
