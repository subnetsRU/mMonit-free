<?php
/*
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
$pathinfo = dirname(__FILE__);
require_once(realpath(sprintf("%s/../func.php",$pathinfo)));
$location=preg_replace("/^\//","",$_SERVER['REQUEST_URI']);
//deb("Request: ".print_r($param,true));

if (chk_auth(1)){
    $err=array();
    if (isset($param['data']) && $param['data']){
	$request_params=get_request_params($param['data']);
	if (isset($request_params['error'])){
	    $err=$request_params['error'];
	}else{
	    $param = $request_params['data'];
	    $param['location'] = $location;
	}
	//deb($param);

	if (count($err) == 0){
	    if (!isset($param['act']) || !$param['act']){
		$err[]="Действие неизвестно";
	    }
	}

	if (count($err) > 0){
	    print error($err);
	    unset($param['act']);
	    print goback($param,array("update"=>"workArea"));
	}
    }else{
	$get_hosts=get_hosts();
	if (isset($get_hosts['error'])){
	    $err=$get_hosts['error'];
	}

	if (count($err) == 0){
	    print "<table>";
	    print "<thead>";
		print "<th>#</th>";
		print "<th>Дата обновления</th>";
		print "<th>incarnation</th>";
		print "<th>Хост</th>";
		print "<th>ID хоста</th>";
		print "<th>Версия monit</th>";
	    print "</thead>";
	    $nn=1;
	    foreach($get_hosts['hosts'] as $k=>$v){
		print "<tr class=\"center\">";
		    printf("<td>%d</td>",$nn++);
		    printf("<td>%s</td>",isset($v['data']['fdate']) ? date("d.m.Y H:i:s",$v['data']['fdate']) : "неизвестна");
		    printf("<td>%s</td>",isset($v['data']['host_header']['incarnation']) ? date("d.m.Y H:i:s",$v['data']['host_header']['incarnation']) : "неизвестна");
		    printf("<td>%s</td>",isset($v['name']) ? $v['name'] : "отсутствует");
		    printf("<td>%s</td>",$k);
		    printf("<td>%s</td>",isset($v['data']['host_header']['version']) ? $v['data']['host_header']['version'] : "отсутствует");
		print "</tr>";
	    }
	    print "</table>";
	    //deb($get_hosts);
	}

	if (count($err) > 0){
	    print error($err);
	}
    }
}

?>