<?php
/*
    Configuration file

    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
date_default_timezone_set('Europe/Moscow');

define('LOGIN','admin');
define('PASSWORD','pass_4_monit');
//
define('CHECK_4_NEW_VERSION',true);		//check if new version is avail, value is true or false, default true
define('REFRESH_PAGE',60);			//default page refresh in seconds, default 60
define('NETWORK_ERRORS_AS_ALARM',false);	//treat network errors as a problem, value is true or false, default false
//
define('COLLECTOR_DATA_DIR',"collector/data");	//dir write collector data to
define('COLLECTOR_LOG_REQUEST',false);		//log all requests to collector, value is true or false, default false
define('COLLECTOR_CHECK_TIMER',300);		//check and show collector errors if exist, seconds, default is 300
//
$developer_ips=array(
    "127.0.0.1",
    "172.16.10.39",
    "192.168.0.0/255.255.255.0",
);

//Hosts that allowed to send data to collector
$allowedHosts=array(
    'test.example.ru' => array(
	'1.1.1.1',
	'2.2.2.2',
    ),
    'test2.example.ru' => array(
	'1.1.1.1',
	'3.3.3.3',
    ),
    'test3.example.ru' => '1.2.3.4',
);
//
$sysUsers = array(
    '0'	=> 'root',
    '22' => 'ssh',
    '53' => 'bind',
    '80' => 'www',
    '88' => 'mysql',
    '931' => 'asterisk',
    '65534' => 'nobody',
);

$sysGroups = array(
    '0' => 'wheel',
    '22' => 'ssh',
    '53' => 'bind',
    '69' => 'network',
    '80' => 'www',
    '88' => 'mysql',
    '931' => 'asterisk',
    '65533' => 'nogroup',
    '65534' => 'nobody',
);

?>