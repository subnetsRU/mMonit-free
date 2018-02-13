<?php
/*
    Configuration file

    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
date_default_timezone_set('Europe/Moscow');

define('LOGIN','admin');
define('PASSWORD','admin');

//More info for the developer
$developer_ips=array(
    "127.0.0.1",
    "192.168.255.0/255.255.255.248",
);
//

//Hosts that allowed to send data to collector
$allowedHosts=array(
    '1.1.1.1'=>array(
	"test1.example.ru",
	"test2.example.ru",
    ),
    '2.2.2.2'=>"test3.example.ru",
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