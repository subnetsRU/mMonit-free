<?php
/*
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
require_once(realpath("../func.php"));
global $allowedHosts;

if (COLLECTOR_LOG_REQUEST === true && !defined('LOG')){
    define( 'LOG',@fopen(sprintf("%s/%s/%s_request.log",LOC,COLLECTOR_DATA_DIR,date("Y-m-d",time())),'a+'));
}

$params=array();
if ($_GET){
    $params=$_GET;
}elseif($_POST){
    $params=$_POST;
}
$err=array();

$request = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
$requestID = mtime( );
logg(sprintf("<%s> Connect from: %s method %s",$requestID,REMOTE_ADDR != "0.0.0.0" ? REMOTE_ADDR : "unknown",isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : "unknown"));
if (count($params) > 0){
    logg(sprintf("<%s> Method data: %s",$requestID,count($params) > 0 ? print_r($params,true) : "none"));
}
logg(sprintf("<%s> REQUEST: %s",$requestID,$request));

if (!$request){
    $err[]="You dont send any data";
}else{
    if (!isset($allowedHostsIps[REMOTE_ADDR])){
	$err[]="Access denied";
    }
}

$data=unserialize_xml($request);
if (count($err) == 0){
    if (is_array($data) && count($data) > 0){
	if (!isset($data['@attributes']['id']) || !isset($data['server']['localhostname'])){
	    $err[]="Identification failed";
	}
    }else{
	$err[]="Cant read your data";
    }
}

if (count($err) == 0){
    $host_name=trim($data['server']['localhostname']);
    if (!isset($allowedHosts[$host_name])){
	$err[]="is not allowed here";
    }
}

if (count($err) == 0){
    $data['host_header']=$data['@attributes'];
    unset($data['@attributes']);
    $host = array(
	'ip' => REMOTE_ADDR,
	'name' => $host_name,
	'id' => trim($data['host_header']['id']),
    );
    $host['file']=sprintf("%s/%s/host_%s_%s.json",LOC,COLLECTOR_DATA_DIR,$host['name'],$host['id']);
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

    //Log errors for show up in iface
    write_file(
	array(
	    'file' => sprintf("%s/%s/errors.log",LOC,COLLECTOR_DATA_DIR),
	    'mode' => 'a+',
	    'data' => sprintf("[%s] Host %s (%s)%s\n",date("d.m.Y H:i:s",time()),REMOTE_ADDR,isset($data['server']['localhostname']) ? $data['server']['localhostname'] : "hostname unknown",sprintf(" %s",implode("; ",$err))),
	)
    );
    //
}else{
    $result = sprintf("<%s> OK\n",$requestID);
    logg($result);
    print $result;
}

//Clean up error file
collector_check_4_errors( 1 );

?>