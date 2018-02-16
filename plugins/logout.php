<?php
/*
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
$pathinfo = dirname(__FILE__);
require_once(realpath(sprintf("%s/../func.php",$pathinfo)));

@session_destroy();
print "<script>";
printf("window.location.href='%sindex.php';",URL);
print "</script>";

?>