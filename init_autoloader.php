<?php
// Composer autoloading
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    $loader = include dirname(__FILE__) . '/vendor/autoload.php';
} else {
	throw new \RuntimeException("No vendor/autoload.php, please run composer.phar update.");
}

