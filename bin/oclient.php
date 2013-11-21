<?php

use Symfony\Component\Console\Application;
use OClient\Command;
use Zend\Config\Config;
use Zend\Loader;

$base_path = realpath(dirname(__FILE__) . '/../');

require_once($base_path . '/init_autoloader.php');
/*
if (file_exists($base_path . '/vendor/autoload.php')) {
    $loader = include $base_path . '/vendor/autoload.php';
} else {
	throw new \RuntimeException("Missing vendor/autoload.php, use php composer.phar update or install.");
}
*/
if (!file_exists($base_path . '/config/oclient.config.php')) {
	throw new \RuntimeException("Missing config/oclient.config.php.");
}

$config = new Config(
		array_merge(
				include $base_path . '/config/oclient.config.defaults.php',
				include $base_path . '/config/oclient.config.php'
				)
		);


$console = new Application();
$console->add(new Command\ProductBrandRetrieveCommand($config));
$console->add(new Command\ProductPictureRetrieveCommand($config));

$console->run();