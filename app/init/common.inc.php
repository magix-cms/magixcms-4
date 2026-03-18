<?php
/*$config = dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php';
if (file_exists($config)) {
    require $config;
}*/
/**
 * Ini name data Mysql
 */
if(defined('MP_LOG')){
    $dis_errors = match (MP_LOG) {
        'log' => 1,
        default => 0,
    };
    ini_set('display_errors', $dis_errors);
    ini_set('display_startup_errors', $dis_errors);
    error_reporting(E_ALL);
}