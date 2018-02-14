<?php
/*
    Constants file
    
    !!! DO NO EDIT !!!
    
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
setlocale(LC_TIME, "ru_RU.UTF-8");
setlocale(LC_ALL, array("ru_RU.UTF-8"));
ini_set('default_charset','UTF-8');

define('VERSION','1.0.0');
define('SYSTEM_NAME','mMonit-free');
//
define('HOST',isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost");
$proto = "http";
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']){
    $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'];
}elseif(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
    $proto = "https";
}
define('PROTO',$proto);
define('URL',sprintf("%s://%s/",PROTO,HOST));
define('REMOTE_ADDR',(isset($_SERVER['HTTP_X_REAL_IP']) && $_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ""));
//
define('LOC',dirname(__FILE__));
//
define('CHARSET','utf8');
define('REPLACE_FLAGS', ENT_COMPAT | ENT_XHTML);
//
$services=array(
    '5' => array(
	'name' => "Система",
	'key' => 'system',
    ),
    '0' => array(
	'name' => "Файловая система",
	'key' => 'filesystem',
    ),
    '3' => array(
	'name' => "Процессы",
	'key' => 'process',
    ),
    '8' => array(
	'name' => "Сеть",
	'key' => 'net',
    ),
    '1' => array(
	'name' => "Проверка директорий",
	'key' => 'directory',
    ),
    '2' => array(
	'name' => "Проверка файлов",
	'key' => 'file',
    ),
    '4' => array(
	'name' => 'Проверка хостов',
	'key' => 'host',
    ),
    '7' => array(
	'name' => 'Проверка скриптов',
	'key' => 'program',
    ),
    '-1' => array(
	'name' => "Неизвестные",
	'key' => 'unknown',
    ),
);

?>