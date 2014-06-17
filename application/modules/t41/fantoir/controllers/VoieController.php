<?php

use Maell\Backend;

use Maell\Core\Layout;
use Maell\View;
use Maell\ObjectModel\ObjectUri;
use Maell\ObjectModel;
use Maell\View\FormComponent;
use Maell\View\ListComponent;
use Maell\ObjectModel\Collection;

require_once ('vendor/maellio/maell/controllers/CrudController.php');
require_once ('modules/t41/fantoir/models/IO/Fantoir/Voie.php');

class Fantoir_VoieController extends \Maell_CrudController {


        protected $_class = 'IO\Fantoir\Voie';

        
        public function indexAction()
        {
                $collection = new Collection($this->_class);
        
                if ($this->_getParam('id')) {
                        $collection->having(ObjectUri::IDENTIFIER)->equals($this->_getParam('id'));
                        $collection->setBoundaryBatch(1)->find();
                        Zend_Debug::dump(Backend::getLastQuery());
                        
                        if ($collection->getTotalMembers() == 1) {
                                $machine = $collection->getMember(Collection::POS_FIRST);
        
                                $form = new FormComponent($machine);
                                $form->setDecorator('View')->setTitle('Détails de la machine')->register();
        
                                $listeinterfaces = new ListComponent($machine->getInterfaces());
                                $listeinterfaces->setTitle('Interfaces Zabbix sur cette machine')->register();
                        }
        
        
        
                } else {
        
                        $search = new FormComponent(new IO\Fantoir\Voie());
                        $search->setParameter('display', array('libvoie', 'ccom'));
                        $search->setDecorator('search')->register();
        
                        $list = new ListComponent($collection);
                        $list->addRowAction($_SERVER['REQUEST_URI'], 'Read', array('icon' => 'tool-blue'));
                        $list->register();
                }
        }
}