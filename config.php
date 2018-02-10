<?php
/*
    Configuration file

    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/

define('LOGIN','admin');
define('PASSWORD','admin');

//More info for the developer
$developer_ips=array(
    "127.0.0.1",
    "192.168.255.0/255.255.255.248",
);
//

//Hosts that allowed to post data
$allowedHosts=array(
    '1.1.1.1'=>array(
	"test1.example.ru",
	"test2.example.ru",
    ),
    '2.2.2.2'=>"test3.example.ru",
);
//

?>