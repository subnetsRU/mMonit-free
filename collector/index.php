<?php
/*
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
require_once(realpath("../func.php"));
global $allowedHosts;
$debug = 0;

if ($debug && !defined('LOG')){
    define( 'LOG', fopen(sprintf("%s/collector/data/%s_request.log",LOC,date("Y-m-d",time())),'a+'));
}

$out=array();
if ($_GET){
    $out=$_GET;
}elseif($_POST){
    $out=$_POST;
}
$err=array();

$request = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : "";
$requestID = mtime( );
logg(sprintf("<%s> Connect from: %s method %s",$requestID,defined('REMOTE_ADDR') ? REMOTE_ADDR : "unknown",isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : "unknown"));
if (count($out) > 0){
    logg(sprintf("<%s> Method data: %s",$requestID,count($out) > 0 ? print_r($out,true) : "none"));
}
logg(sprintf("<%s> REQUEST: %s",$requestID,$request));

if (!$request){
    $err[]="You dont send any data";
}else{
    if (!isset($allowedHosts[REMOTE_ADDR])){
	$err[]="Access denied";
    }
}

if (count($err) == 0){
    $data=unserialize_xml($request);
    if (is_array($data) && count($data) > 0){
	if (!isset($data['@attributes']['id']) || !isset($data['server']['localhostname'])){
	    $err[]="Identification failed";
	}
    }else{
	$err[]="Cant read your data";
    }
}

if (count($err) == 0){
    $data['host_header']=$data['@attributes'];
    unset($data['@attributes']);
    if (!is_array($allowedHosts[REMOTE_ADDR])){
	$allowedHosts[REMOTE_ADDR] = array( $allowedHosts[REMOTE_ADDR] );
    }
    foreach ($allowedHosts[REMOTE_ADDR] as $host){
	if ($host == $data['server']['localhostname']){
	    $host = array(
		'ip' => REMOTE_ADDR,
		'name' => $host,
		'id' => $data['host_header']['id'],
	    );
	    $host['file']=sprintf("%s/collector/data/host_%s_%s.json",LOC,$host['name'],$host['id']);
	    break;
	}else{
	    unset($host);
	}
    }
    
    if (!isset($host)){
	$err[]=sprintf("Host %s is not allowed here",$data['server']['localhostname']);
    }
}

if (count($err) == 0){
    logg($host);
    $write=write_file(
	array(
	    'file' => $host['file'],
	    'mode' => 'w',
	    'data' => jsonEncode($data),
	)
    );
    if (isset($write['error'])){
	$err=$write['error'];
    }
}

if (count($err) > 0){
    $result = sprintf("<%s> ERROR:\n\t* %s",$requestID,implode("\n\t* ",$err));
    logg($result);
    print $result;
}else{
    $result = sprintf("<%s> OK\n",$requestID);
    logg($result);
    print $result;
}

?>