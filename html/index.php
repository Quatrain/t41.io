<?php

use Maell as Maell;
use Maell\Config;
use Maell\View;
use Maell\View\SimpleComponent;

/**
 * This is your application bootstrap
 * 
 * Its role is to:
 * - initiate environment: adjust include paths, load mandatory vendors modules, enable autoloader...
 * - load application configuration & modules
 * - route the user's request
 * - render view part
 */

ini_set('display_errors', true);

require_once '../vendor/autoload.php';

/* STEP 1: detect and load core components (t41 + zf) */
require_once '../vendor/maellio/Maell/library/maell.php';
Maell::setIncludePaths(substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), '/')+1), true);
Maell::enableAutoloader(array('Maell' => substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), '/')+1) . 'application/library'
							,'Zend_' => Maell::$basePath . 'vendor/zendframework/zendframework1/library')
						  );

Maell::sendNoCacheHeaders();

/* STEP 2: load application configuration and modules */ 
Maell::init();
Maell::$lang = 'fr';

require 'application/library/IO.php';
require 'application/library/IO/Exception.php';

/* STEP 3: route request */
$fcontroller = \Zend_Controller_Front::getInstance();
$fcontroller->throwExceptions(true);
$fcontroller->setParam('noViewRenderer', true);
$_routes = array();

foreach (Config::getPaths(Config::REALM_CONTROLLERS) as $controller) {
	list($path, $prefix) = explode(Config::PREFIX_SEPARATOR, $controller);
	$_routes[$prefix] = $path;
}
$fcontroller->setControllerDirectory($_routes);


try {
	$fcontroller->dispatch();
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
	//$erreur->setContent(sprintf('<h2>%s</h2><br/>', $e->getMessage()));
	$erreur->setTitle($title . " : " . $e->getMessage())->register();
	
	if (Maell::$env != Maell::ENV_PROD) {
		$erreur->setContent(sprintf('<blockquote style="font: 11px courier">%s</blockQuote>'
									, nl2br($e->getTraceAsString())));
	}
}
