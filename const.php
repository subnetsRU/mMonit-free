<?php
/*
    Constants file
    
    !!! DO NO EDIT !!!
    
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
date_default_timezone_set('Europe/Moscow');
setlocale(LC_TIME, "ru_RU.UTF-8");
setlocale(LC_ALL, array("ru_RU.UTF-8"));
ini_set('default_charset','UTF-8');

define('VERSION','0.2.0');
define('SYSTEM_NAME','mMonit-free');
//
define('LOC',dirname(__FILE__));
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
define('CHARSET','utf8');
define('REPLACE_FLAGS', ENT_COMPAT | ENT_XHTML);

?>