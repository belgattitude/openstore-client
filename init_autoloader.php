<?php
// Composer autoloading
if (file_exists('vendor/autoload.php')) {
    $loader = include 'vendor/autoload.php';
} else {
	throw new \RuntimeException("No vendor/autoload.php, please run composer.phar update.");
}

