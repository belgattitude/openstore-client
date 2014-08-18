<?php
// Composer autoloading
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    $loader = include dirname(__FILE__) . '/vendor/autoload.php';
} elseif (file_exists(dirname(__FILE__) . '/../../autoload.php')) {
    $loader = include dirname(__FILE__) . '/../../autoload.php';    
} else {
    throw new \RuntimeException("No vendor/autoload.php, please run composer.phar update.");
}

