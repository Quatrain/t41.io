<?php

use Maell\View\SimpleComponent;
use Maell\View;
use Maell\Controller;

/**
 * This is your application bootstrap
 * 
 * Its role is to:
 * - initiate environment: adjust include paths, load mandatory vendors modules, enable autoloader...
 * - load application configuration & modules
 * - route the user's request
 * - render view part
 */

require_once '../vendor/autoload.php';
require_once '../vendor/maellio/Maell/library/Maell.php';

Maell::setIncludePaths(substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), '/')+1), true);
Maell::enableAutoloader(array('Maell' => substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), '/')+1) . 'application/library'
							, 'IO' => substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), DIRECTORY_SEPARATOR)+1) 
							, 'Zend_' => Maell::$basePath . 'vendor/zendframework/zendframework1/library')
);

Maell::sendNoCacheHeaders();

Maell::init();
Maell::$lang = 'fr';

require 'application/library/IO.php';
require 'application/library/IO/Exception.php';


/* STEP 3: route request */
try {
	Controller::dispatch();
} catch (\IO\Exception $e) {
	displayException($e);	
} catch (\Exception $e) {
	displayException($e, "Erreur Système");
}

/* STEP 4: prepare & execute view rendering */


echo View::display();


/**
 * Affiche les exceptions sous forme d'un composant
 * @param Exception $e
 * @param string $title
 */
function displayException($e, $title='Erreur Applicative')
{
	View::resetObjects(View::PH_DEFAULT);
	$erreur = new SimpleComponent();
	$erreur->setTitle($title . " : " . $e->getMessage())->register();
	
	if (Maell::$env != Maell::ENV_PROD) {
		$erreur->setContent(sprintf('<blockquote style="font: 11px courier">%s</blockQuote>'
									, nl2br($e->getTraceAsString())));
	}
	View::setTemplate('default.html');
	exit(View::display());
}
