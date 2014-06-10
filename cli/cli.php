<?php

use t41\Controller\Router\CliRouter;

use t41\Config;
use t41\Core;

error_reporting(E_ALL);
ini_set('display_errors', true);

/* STEP 1: detect and load core components (t41 + zf) */
require_once '../vendor/quatrain/t41/library/t41/Core.php';
\Maell::setIncludePaths(substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), '/')+1));
\Maell::enableAutoloader(array('CuertoCs' => substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), '/')+1) . 'application/library'
							,'Zend_' => \Maell::$basePath . 'vendor/zendframework/zf1/library')
						  );

/* STEP 2: load application configuration and modules */ 
\Maell::init();
\Maell::$lang = 'fr';

require 'application/library/CuertoCs.php';
require 'application/library/CuertoCs/Exception.php';

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

/* @todo find something smarter than getting action and controller parameters from defined constants */
$request = new Zend_Controller_Request_Simple(CLI_ACTION, CLI_CONTROLLER, CLI_MODULE, CLI_PARAMS);

$ctrl = Zend_Controller_Front::getInstance();
$ctrl->setRouter(new CliRouter());

$ctrl->throwExceptions(true);
$ctrl->setParam('noViewRenderer', true);

$ctrl->dispatch($request);

