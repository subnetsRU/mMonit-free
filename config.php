<?php
/*
    Configuration file

    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/

define('LOGIN','admin');
define('PASSWORD','admin');

$developer_ips=array(
    "127.0.0.1",
    "91.217.137.248/255.255.255.248",
    "172.16.10.0/255.255.255.0",
    "89.23.62.144/255.255.255.248","89.23.62.192/255.255.255.248",
    "2001:67c:13e4:1::1","2001:67c:13e4:1000::cc","2001:67c:13e4:1000::37"
);

$allowedHosts=array(
    '91.217.137.35'=>array(
	"work.mega-net.ru",
    ),
    '2001:67c:13e4:2::2'=>array(
	"work.mega-net.ru",
    ),
    '2001:67c:13e4:403::241' => "subnets.mega-net.ru",
    '172.16.10.39'=>"virus.mega-net.ru",
);

?>