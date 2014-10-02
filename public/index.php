<?php

 /* ini_set ( 'session.save_handler', "memcache" ); 
 ini_set ( 'session.save_path', "tcp://172.31.31.240:11211" );
 ini_set ( 'memcache.hash_strategy', "consistent" );
 ini_set ( 'memcache.allow_failover', "1" );
 ini_set ( 'memcache.session_redundancy', "2" ); */
 
date_default_timezone_set('America/Sao_Paulo');

    error_reporting(E_ALL);
    ini_set('display_errors', true);

/**
 * This makes our life easier when dealing with paths./
 * Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' &&
         is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
