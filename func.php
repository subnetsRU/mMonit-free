<?php
/*
    Functions file

    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
$err=array();
$const=realpath( dirname(__FILE__) )."/config.php";
if (is_file($const)){
    if (is_readable($const)){
	if (!require_once($const)){
	    $err[]="CONFIG file required";
	}
    }else{
	$err[]="Can`t read CONFIG file";
    }
}else{
     $err[]="CONFIG file don`t exists";
}

$const=realpath( dirname(__FILE__) )."/const.php";
if (is_file($const)){
    if (is_readable($const)){
	if (!require_once($const)){
	    $err[]="CONST file required";
	}
    }else{
	$err[]="Can`t read CONST file";
    }
}else{
     $err[]="CONST file don`t exists";
}

if (!defined('LOGIN') || !defined('PASSWORD')){
    $err[]=sprintf("В конфигурации отсутствуют логин или пароль%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
}else{
    if (!LOGIN || !PASSWORD){
	$err[]=sprintf("В конфигурации логин или пароль пусты%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
    }
}

if (count($err) == 0){
    define('AUTH',md5(sprintf("%s%s",LOGIN,PASSWORD)));
}else{
    print head("",0);
    print error($err,"Критические ошибки");
    print foot();
    exit(0);
}


if (!defined('REMOTE_ADDR')){
    define('REMOTE_ADDR','0.0.0.0');
}

if (!is_developer()){
    ini_set('display_errors', 'off');
    error_reporting( 0 );
}else{
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
    set_error_handler("exception_error_handler");
    //register_shutdown_function( "check_for_fatal" );
}

if (!defined('CLI_RUN')){
    session_set_cookie_params(60 * 60 * 24 * 30,"/",HOST);
    ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
    if(session_status() == PHP_SESSION_NONE){
        session_start();
    }
}

$param=array();
if (isset($_GET) && $_GET){
    $param=$_GET;
}elseif(isset($_POST)){
    $param=$_POST;
}

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    if (is_developer()){
	if( defined('DEBUG') && DEBUG ){
	    //http://php.net/manual/ru/errorfunc.constants.php
	    if ($errno != 2){	//exept E_WARNING
		if ( PHP_SAPI === 'cli'){
		    print debug_print_backtrace( );
		}else{
		    print "<pre>";
		    ob_start();
		    debug_print_backtrace( );
		    $dump = ob_get_clean();
		    print @htmlspecialchars($dump, REPLACE_FLAGS, CHARSET);
		}
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		if ( PHP_SAPI === 'cli'){
		    print "\n";
		}else{
		    print "</pre>";
		}
	    }
	}
	return;
    }
}

function check_for_fatal(){
    $error = error_get_last();
    if ($error){
	deb("PHP shutdown:");
	deb($error);
    }
}

function log_exception( Exception $e ){
    deb($e);
}

function is_developer($search_type="all"){
    global $developer_ips;
    if (!is_array($developer_ips)){ $developer_ips = array(); }
    $dev=0;

    if (PHP_SAPI === 'cli'){
	//console scripts
	$dev=1;
    }else{
	$ip=REMOTE_ADDR;
	$uid=isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
	$matchIP = matchIP($search_type);
	if ($matchIP == "ip"){
	    $search_type="ip";
	    $search_ip=$tmp[1];
	}elseif ($matchIP == "ip6"){
	    $search_type="ip";
	    $search_ip=$tmp[1];
	}else{
	    $search_ip=$ip;
	}

	if ( $search_type == "all" || $search_type == "uid" ){
	    if ((int)$uid == 1){
		$dev=1;
	    }
	    if ((int)$uid > 0 && isset($_SESSION['user']['group']) && $_SESSION['user']['group'] == 1){
		$dev=1;
	    }
	}

	if ( $dev==0 && ($search_type == "all" || $search_type == "ip" ) ){
    	    foreach ($developer_ips as $item){
    		$tmp=explode("/",$item);
    		if (count($tmp) > 1){
    		    if (ip_vs_net($search_ip,$tmp[0],$tmp[1])){
    			$dev=1;
    			break;
    		    }
    		}else{
        	    if ($search_ip == $item){
            		$dev=1;
            		break;
        	    }
        	}
    	    }
    	}
    }

 return $dev;
}

function deb($text){
    $notcli=1;
    if (PHP_SAPI === 'cli'){
	$notcli=0;
    }
    if (is_developer()){
	if ($notcli){print "<pre>";}
	print "[DEBUG] ";
	if (is_array($text)){
	    foreach ($text as $k=>$v){
		if (is_array($v)){
		    printf("<b>[%s] => array</b>\n",$k);
		    print_r($v);
		}else{
		    printf("[%s] => %s\n",$k,is_bool($v) ? var_export($v,true) : $v);
		}
	    }
	}else{
	    printf ("%s\n",is_bool($text) ? var_export($text,true) : $text);
	}
	if ($notcli){print "</pre>";}
    }else{
	//print "NOT A DEV\n";
    }
}

function mtime( $micro = ""){
    $mtime = microtime( true );
    if ($micro){
	$mtime = explode(".",$mtime);
	return $mtime[1];
    }
 return $mtime;
}

function MONITstripcslashes($input){
    return @stripcslashes($input);
}

function MONIThtmlspecialchars($input){ 
    return @htmlspecialchars($input, REPLACE_FLAGS, CHARSET); 
}

function unserialize_xml($input, $callback = null, $recurse = false){
	global $custom_error, $data;
        //URL: http://php.net/manual/ru/function.simplexml-load-string.php
        //install /usr/ports/textproc/php5-simplexml
        if ((!$recurse) && is_string($input)){
            $pre_data=preg_replace('/&/', '&amp;', $input);
            if( ( $result = @simplexml_load_string($pre_data) ) === false ){
                $custom_error = 'Error during parse of XML. Please check your request params and don`t forget about param value encoding: UTF-8';
                $data['error'] = 800;
            }
        }else{
            $result=$input;
        }
        if ($result instanceof SimpleXMLElement){
            if (count((array)$result)>0){
                $result = (array) $result;
            }
        }
        if (is_array($result)) foreach ($result as &$item) $item = unserialize_xml($item, $callback, true);
        return (!is_array($result) && is_callable($callback))? call_user_func($callback, $result): $result;
}

function ip_vs_net($ip,$network,$mask){
   if (((ip2long($ip))&(ip2long($mask)))==ip2long($network)){
        return 1;
    }else{
        return 0;
    }
}

function cidr_2_mask($mask){
    return long2ip(pow(2,32) - pow(2, (32-$mask)));
}

function mask_2_cidr($mask){
    $a=strpos(decbin(ip2long($mask)),"0");
    if (!$a){$a=32;}
    return $a;
}

function matchIP( $text ){
    $ret="";
    if (preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/",$text)){
	$ret="ip";
    }
    if (preg_match("/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/",$text)){
	$ret="ip6";
    }
 return $ret;
}

function write_file( $p = array() ){
    $ret=array();
    $err=array();
    if (!isset($p['file'])){
	$err[]=sprintf("File to write is unknown%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
    }
    if (!isset($p['data'])){
	$err[]=sprintf("No data to write%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
    }
    if (!isset($p['mode'])){
	$p['mode'] = "w";
    }
    
    if (count($err) == 0){
	$path=pathinfo( $p['file'] );
	if (!is_dir($path['dirname'])){
	    $err[]=sprintf("Directory%s not exists or is file%s",is_developer() ? " ".$path['dirname'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}else{
	    if (!file_exists($p['file'])){
		if (!is_writable($path['dirname'])){
		    $err[]=sprintf("Directory%s is not writable%s",is_developer() ? " ".$path['dirname'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
		}
	    }else{
		if (!is_writable($p['file'])){
		    $err[]=sprintf("File%s is not writable%s",is_developer() ? " ".$p['file'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
		}
	    }
	}
    }

    if (count($err) == 0){
	$handle=@fopen($p['file'],$p['mode']);
	if ( $handle !== false){
	    if (@fwrite($handle,$p['data']) === false){
		$err[]=sprintf("Cant write file%s%s",is_developer() ? " ".$p['file'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	    }else{
		@fclose($handle);
	    }
	}else{
	    $err[]=sprintf("Cant open file%s for writing%s",is_developer() ? " ".$p['file'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}
    }

    if (count($err) > 0){
	$ret['error'] = $err;
    }
 return $ret;
}

function jsonDecode( $json ){
    return json_decode($json,true,512);
}

function jsonEncode( $array ){
    return json_encode($array,JSON_FORCE_OBJECT);
}

function logg( $text ){
    if( is_resource( LOG ) ){
	$string=sprintf( "[%s]: %s\n", date( "d.m.Y H:i:s", time( ) ), is_array($text) ? print_r($text,true) : $text );
	fputs( LOG, $string );
    }
}

function read_dir( $p = array() ){
    $ret = array();
    $err = array();
    
    if (!isset($p['dir'])){
	$err[]=sprintf("Директория не задана%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
    }
    if (count($err) == 0){
	$sdir=sprintf("%s/%s",LOC,$p['dir']);
	if (is_dir($sdir)){
	    if ($dir = opendir($sdir)){
		$ret['list'] = array();
		while (false !== ($file = readdir($dir))){
		    if ($file != "." && $file != ".."){
			if (preg_match("/\.(txt|json|xml)$/",$file)){
			    if ( !isset($p['filter']) || (isset($p['filter']) && preg_match(sprintf("/%s/",$p['filter']),$file)) ){
				$ret['list'][]=$file;
			    }
			}
		    }
		}
	    }else{
		$err[]=sprintf("Директория%s не читается%s",is_developer() ? " ".$sdir : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	    }
	}else{
	    $err[]=sprintf("Директория%s не найдена%s",is_developer() ? " ".$sdir : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}
    }

    if (count($err) > 0){
	$ret['error']=$err;
    }
 return $ret;
}

function read_host_file( $p = array() ){
    $ret = array();
    $err = array();
    
    if (!isset($p['file'])){
	$err[]=sprintf("Файл хоста не передан%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
    }

    if (count($err) == 0){
	if (!file_exists($p['file'])){
	    $err[]=sprintf("Файл хоста%s не найден%s",is_developer() ? " ".$p['file'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}
    }

    if (count($err) == 0){
	if (!is_readable($p['file'])){
	    $err[]=sprintf("Отсутствуют права доступа на чтение файл хоста%s%s",is_developer() ? " ".$p['file'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}
    }

    if (count($err) == 0){
	$tmp=file_get_contents($p['file']);
	if ($tmp === false){
	    $err[]=sprintf("Невозможно прочитать файл хоста%s%s",is_developer() ? " ".$p['file'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}else{
	    $rf=jsonDecode($tmp);
	    if ($rf === NULL || $rf === FALSE){
		$err[]=sprintf("Данные в файле хоста отсутствуют%s%s",is_developer() ? " ".$p['file'] : "",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	    }else{
		$ret['data']=$rf;
		$ret['data']['fdate']=filemtime($p['file']);
	    }
	}
    }

    if (count($err) > 0){
	$ret['error']=$err;
    }
 return $ret;
}

////////////////////// WEB ///////////////////////////////
function head($title="",$full_head=1){
	$system_name=(defined('SYSTEM_NAME') && SYSTEM_NAME) ? SYSTEM_NAME : "";
	if (!$title){
	    $title=$system_name;
	}
	$head="<html>\n";
	$head.=sprintf("<title>%s</title>\n",$title);
	$head.="<head>\n";
	$head.="<meta charset=\"utf-8\">\n";
	$head.="<META HTTP-EQUIV=\"PRAGMA\" CONTENT=\"NO-CACHE\">\n";
	$head.="<META HTTP-EQUIV=\"CACHE-CONTROL\" CONTENT=\"NO-CACHE\">\n";
	$head.="<META HTTP-EQUIV=\"expires\" content=\"Mon, 01 Jan 1990 00:00:00 GMT\">\n";
	$head.="<META HTTP-EQUIV=\"Content-language\" CONTENT=\"ru\">\n";
	if ($system_name){
	    $head.='<meta name="description" content="'.$system_name.'">'."\n";
	    $head.='<meta name="keywords" content="'.$system_name.'">'."\n";
	    $head.='<meta name="abstract" content="'.$system_name.'">'."\n";
	    $head.='<meta name="page-topic" content="'.$system_name.'">'."\n";
	}
	$head.='<meta name="copyright" content="Meganet-2003 :: www.mega-net.ru">'."\n";
	$head.='<meta name="author" content="Meganet-2003 :: www.mega-net.ru">'."\n";
	$head.='<meta name="document-state" content="dynamic">'."\n";
	$head.=sprintf("<link rel=\"SHORTCUT ICON\" href=\"%simg/favicon.ico\">\n",URL);
	if ($full_head){
	    $head.=sprintf("<script type=\"text/javascript\" src=\"%sjs/mt151_c.js\"></script>\n",URL);
	    $head.=sprintf("<script type=\"text/javascript\" src=\"%sjs/main.js.php?v=0.1\"></script>\n",URL);
	}
	$head.=sprintf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%scss/monit.css?v=0.1\">\n",URL);
        $head.="</head>\n";
	$head.="<body id=\"body\">\n";
 return $head;
}

function foot(){
    return "\n</body>\n</html>\n";
}

function error( $text, $header = "" ){
    if (is_array($text)){
	$tmp=$text;
	if (count($tmp)>1){
	    $text="<ul>";
	    foreach ($tmp as $v){
		$text.=sprintf("<li>%s</li>",$v);
	    }
	    $text.="</ul>";
	}else{
	    $text=$tmp[0];
	}
    }
    return sprintf("<div class=\"error\">%s%s</div>",$header ? "<b>".$header.":</b><HR>" : "",$text);
}

function get_request_params($data =""){
    $ret = array();
    $err = array();

    if (!$data){
	$err[]=sprintf("Параметры запроса отсутствуют%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
    }

    if (count($err) == 0){
	$decode=base64_decode($data);
	if ($decode === false){
	    $err[]=sprintf("Неверные параметры запроса%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}
    }

    if (count($err) == 0){
	$expl = explode("&",$decode);
	foreach ($expl as $str){
	    $tmp = explode("=",$str);
	    if (count($tmp) == 2){
		if (!isset($params)){
		    $params = array();
		}
		$params[$tmp[0]]=$tmp[1];
	    }
	}

	if (!isset($params) || !is_array($params)){
	    $err[]=sprintf("Параметры запроса пусты%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}
    }

    if (count($err) == 0){
	$ret['data']=$params;
    }

    if (count($err) > 0){
	$ret['error'] = $err;
    }

 return $ret;
}

function chk_auth( $fatal = 0){
    if (isset($_SESSION['auth']) && $_SESSION['auth'] == 1){
	return 1;
    }else{
	if ($fatal){
	    print error("Вы не авторизованы");
	    printf("\n<script>setTimeout(function(){window.location.href='%s';},'3000');</script>\n",URL);
	    exit(0);
	}
    }
 return 0;
}

function goback( $request, $funcParam =array() ){
    $update = "";
    $ret = "";
    $location = "index.php";

    if (isset($request['location'])){
	$location = $request['location'];
    }
    unset($request['data'],$request['location']);

    $timeout=3000;
    if (isset($funcParam['timeout']) && preg_match("/^\d+$/",$funcParam['timeout']) && (int)$funcParam['timeout'] > 0){
	$timeout=$funcParam['timeout'];
	if ($timeout < 1000){
	    $timeout=$timeout*1000;
	}
    }

    $ret.="\n<script>\n";
    if (isset($funcParam['update']) && $funcParam['update']){
	if ($funcParam['update']){
	    $update = sprintf("'%s'",$funcParam['update']);
	}
    }

    $ret.="setTimeout(function(){";
    if ($update){
	$ret.=sprintf("MONIT.mJson( {
	    id: %s,
	    location: '%s',
	    request: '%s',
	} );\n",$update,$location,http_build_query($request));
    }
    $ret.=sprintf("},'%d');\n",$timeout);
    $ret.="</script>";
 return $ret;
}

function menu(){
    $ret = array();
    if (isset($_SESSION['auth']) && $_SESSION['auth'] == 1){
	$ret = array(
	    'exit' => array(
		'location' => 'plugins/logout.php',
		'class' => 'exit',
		'text' => 'Выход',
	    )
	);
    }
 return $ret;
}

function get_hosts(){
    $ret = array();
    $err = array();
    $host_data_dir="collector/data";

    $list=read_dir(
	array(
	    "dir"=>$host_data_dir,
	    "filter"=>"^host_",
	)
    );

    if (isset($list['error'])){
	$err=$list['error'];
    }

    if (count($err) == 0){
	if (!isset($list['list']) || !is_array($list['list']) || count($list['list']) == 0){
	    $err[]=sprintf("Хостов не найдено%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}
    }

    if (count($err) == 0){
	$ret['hosts']=array();
	foreach ($list['list'] as $k=>$f){
	    if (preg_match("/^host_(\S+)_(\S+)\.json$/",$f,$m)){
		$ret['hosts'][$m[2]]=array("name"=>$m[1],"file"=>$f);
		$rf=read_host_file(array("file"=>sprintf("%s/%s/%s",LOC,$host_data_dir,$f)));
		if (isset($rf['error'])){
		    $ret['hosts'][$m[2]]['error']=$rf['error'];
		}else{
		    $ret['hosts'][$m[2]]['data']=$rf['data'];
		}
	    }
	}
	if (count($ret['hosts']) == 0){
	    $err[]=sprintf("Хосты отсутствуют%s",is_developer() ? sprintf(" (file: %s, func: %s, line: %s)",__FILE__,__FUNCTION__,__LINE__) : "");
	}
    }

    if (count($err) > 0){
	$ret['error']=$err;
    }
 return $ret;
}
?>