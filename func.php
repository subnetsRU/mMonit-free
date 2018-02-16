<?php
/*
    Functions file

    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
set_error_handler("exception_error_handler");

$err=array();
$const=dirname(__FILE__)."/config.php";
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

$const=dirname(__FILE__)."/const.php";
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

if (count($err) == 0){
    if (!defined('LOC') || !LOC){
	$err[]=sprintf("Расположение файлов проекта неизвестно%s",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
    }else{
	if (!is_dir(LOC)){
	    $err[]=sprintf("Директория с файлами проекта не нейдена%s",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
	}
    }
}

if (count($err) == 0){
    if (!defined('LOGIN') || !defined('PASSWORD')){
	$err[]=sprintf("В конфигурации отсутствуют логин или пароль%s",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
    }else{
	if (!defined('LOGIN') || !defined('PASSWORD') || !LOGIN || !PASSWORD){
	    $err[]=sprintf("В конфигурации логин или пароль пусты%s",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
	}
    }

    if (!defined('COLLECTOR_DATA_DIR') || !COLLECTOR_DATA_DIR){
	$err[]=sprintf("В конфигурации не указана директория с данными%s",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
    }else{
	$dataDir=sprintf("%s/%s",LOC,COLLECTOR_DATA_DIR);
	if (!is_dir($dataDir)){
	    $err[]=sprintf("Директории для данных%s несуществует%s",is_developer() ? " ".$dataDir : "",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
	}else{
	    if (!is_writable($dataDir)){
		$err[]=sprintf("Отсутствуют права на запись в директорию для данных%s%s",is_developer() ? " ".$dataDir : "",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
	    }
	}
    }
}

if (count($err) == 0){
    if (!isset($allowedHosts) || !is_array($allowedHosts) || count($allowedHosts) == 0){
	$err[]=sprintf("Отсутствуют разрешенные хосты для сохранения данных%s",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
    }
}

if (count($err) == 0){
    define('AUTH',md5(sprintf("%s%s",LOGIN,PASSWORD)));
    if (!isset($sysUsers) || !is_array($sysUsers)){
	$sysGroups=array();
    }
    if (!isset($developer_ips) || !is_array($developer_ips)){
	$developer_ips = array();
    }
    if (!isset($services) || !is_array($services) || count($services) == 0){
	$services = array(  
	    '-1' => array(
		'name' => "Неизвестные",
		'key' => 'unknown',
	    ),
	);
    }
    $allowedHostsIps = array();
    foreach ($allowedHosts as $host_name=>$ip){
	if (is_array($ip)){
	    foreach ($ip as $v){
		if (!isset($allowedHostsIps[$v])){
		    if (matchIP($v)){
			$allowedHostsIps[$v]=$v;
		    }
		}
	    }
	}else{
	    if (!isset($allowedHostsIps[$ip])){
		if (matchIP($ip)){
		    $allowedHostsIps[$ip]=$ip;
		}
	    }
	}
    }
}

if (count($err) == 0){
    if (!isset($allowedHostsIps) || !is_array($allowedHostsIps) || count($allowedHostsIps) == 0){
	$err[]=sprintf("Неизвестны IP-адреса разрешенных для сохранения данных хостов%s",is_developer() ? sprintf(" (file: %s, line: %s)",__FILE__,__LINE__) : "");
    }
}

if (count($err) > 0){
    error_critical($err);
}

if (!defined('REMOTE_ADDR')){
    define('REMOTE_ADDR','0.0.0.0');
}
if (!defined('REFRESH_PAGE')){
    define('REFRESH_PAGE',60);
}
if (!defined('COLLECTOR_LOG_REQUEST')){
    define('COLLECTOR_LOG_REQUEST',false);
}
if (!defined('COLLECTOR_CHECK_TIMER')){
    define('COLLECTOR_CHECK_TIMER',300);
}

if (!is_developer()){
    ini_set('display_errors', 'off');
    error_reporting( 0 );
}else{
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
}

if (!defined('CLI_RUN') || !CLI_RUN){
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
    //http://php.net/manual/ru/errorfunc.constants.php
    if ($errno != 2){	//exept E_WARNING
	if (is_developer()){
		if ( PHP_SAPI === 'cli'){
		    print debug_print_backtrace( );
		}else{
		    ob_start();
		    debug_print_backtrace( );
		    $dump = ob_get_clean();
		    $dump_text = @htmlspecialchars($dump, REPLACE_FLAGS, CHARSET);
		    error_critical("Ошибка PHP кода",$dump_text);
		}
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}else{
		error_critical(sprintf("PHP code %s",$errno));
	}
    }
 return;
}

function is_developer( ){
    global $developer_ips;
    if (!is_array($developer_ips)){ $developer_ips = array(); }
    $dev=0;

    if (PHP_SAPI === 'cli'){
	$dev=1;
    }else{
	if ( matchIP( REMOTE_ADDR ) ){
    	    foreach ($developer_ips as $item){
    		$tmp=explode("/",$item);
    		if (count($tmp) > 1){
    		    if (ip_vs_net(REMOTE_ADDR,$tmp[0],$tmp[1])){
    			$dev=1;
    			break;
    		    }
    		}else{
        	    if (REMOTE_ADDR == $item){
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
    if( defined('LOG') && is_resource( LOG ) ){
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

function mftime( $file = "" ){
    $ret = 0;
    if (file_exists($file)){
	$tmp=trim(filemtime($file));
	if (preg_match("/^\d+$/",$tmp)){
	    $ret = $tmp;
	}
    }
 return $ret;
}

function collector_check_4_errors( $clean = ""){
    $ret = "";
    $error_file=sprintf("%s/%s/errors.log",LOC,COLLECTOR_DATA_DIR);
    if (file_exists($error_file)){
	$mtime=mftime($error_file);
	if ($clean){
	    if ( (time() - $mtime) > (int)COLLECTOR_CHECK_TIMER+120){
		unlink($error_file);
	    }
	}else{
	    if ( (time() - $mtime) < (int)COLLECTOR_CHECK_TIMER ){
		$ret=file_get_contents($error_file);
	    }
	}
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
	    $head.=sprintf("<script type=\"text/javascript\" src=\"%sjs/main.js.php?v=0.3\"></script>\n",URL);
	}
	$head.=sprintf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%scss/monit.css?v=0.3\">\n",URL);
	$head.=sprintf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%scss/megaModal.css?v=0.1\">\n",URL);
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
	    $text="<ul class=\"spisok\">";
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

function error_critical( $err = array(), $dump = ""){
    print head("",0);
    print "<div id=\"centerArea\">";
	print "<div id=\"workArea\" class=\"center\">";
	    print "<div style=\"margin-top: 10%;\">";
		printf("<h1>%s%s</h1>",(defined('SYSTEM_NAME') && SYSTEM_NAME) ? SYSTEM_NAME : "",(defined('VERSION') && VERSION) ? sprintf(" v%s",VERSION) : "");
		print error($err,sprintf("Критическ%s ошибк%s",count($err) == 1 ? "ая" : "ие", count($err) == 1 ? "а" : "и"));
		if ($dump){
		    printf("<HR><code class=\"developer\" style=\"text-align: left;\">%s</code>",$dump);
		}
	    print "</div>";
	print "</div>";
    print "<div>";
    print foot();
    exit(0);
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
	    @session_destroy();
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

    $list=read_dir(
	array(
	    "dir"=>COLLECTOR_DATA_DIR,
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
	$tmp=array();
	$nn=1;
	foreach ($list['list'] as $k=>$f){
	    if (preg_match("/^host_(\S+)_(\S+)\.json$/",$f,$m)){
		$group=get_group($m[1]);
		$key = isset($tmp[$m[1]]) ? sprintf("%s_%d",$m[1],$nn) : $m[1];
		$tmp[$group][$key]=array(
		    'monit_id' => $m[2],
		    'name' => $m[1],
		    'file' => $f,
		    'group' => $group,
		);
		$rf=read_host_file(array("file"=>sprintf("%s/%s/%s",LOC,COLLECTOR_DATA_DIR,$f)));
		if (isset($rf['error'])){
		    $tmp[$group][$key]['error']=$rf['error'];
		}else{
		    $tmp[$group][$key]['data']=$rf['data'];
		}
		$nn++;
	    }
	}
	ksort($tmp);
	foreach ($tmp as $k=>$v){
	    ksort($tmp[$k]);
	}
	foreach ($tmp as $k=>$v){
	    foreach ($v as $hn=>$hv){
		$ret['hosts'][$k][$hv['monit_id']]=$hv;
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

function get_group( $name = ""){
    $ret="z_noname_group";
    if ($name){
	$p=parse_url($name);
	if (isset($p['path']) && $p['path']){
	    $tmp=explode(".",$p['path']);
	    if (count($tmp) > 1){
		if (count($tmp) == 2){
		    $ret=sprintf("%s.%s",$tmp[0],$tmp[1]);
		}else{
		    $ret=sprintf("%s.%s",$tmp[count($tmp)-2],$tmp[count($tmp)-1]);
		}
	    }
	}
    }
 return $ret;
}

function parse_services( $data = array() ){
    global $services,$sysUsers,$sysGroups;
    
    $ret=array();
    foreach ($services as $k=>$v){
	$type[$v['key']] = array();
    }
    $ret['alarm'] = 0;
    //Хост без сервисов
    if (isset($data['service']['@attributes'])){
	$tmp=$data['service'];
	$data['service']=array($tmp);
	unset($tmp);
    }
    //
    foreach($data as $key=>$srv){
	if (is_array($srv)){
	    foreach ($srv as $k=>$v){
		if (isset($v['type'])){
		    $status_2_header = "";
		    $key = isset($services[$v['type']]['key']) ? $services[$v['type']]['key'] : $services[-1]['key'];
		    if (isset($v['status'])){
			if ($v['status'] > 0){
			    $ret['alarm']++;
			    $status_2_header=sprintf("&nbsp;::&nbsp;<span class=\"alarm\">статус %d</span>",$v['status']);
			}
		    }
		    if (!isset($v['monitor'])){
			$v['monitor'] = 0;
		    }
		    if ($v['monitor'] == 0){
			$type[$key][]=sprintf("<h3>%s :: <span class=\"alarm\">Не мониторится</span></h3>",$v['@attributes']['name']);
		    }elseif ($v['monitor'] == 2){
			$type[$key][]=sprintf("<h3>%s :: Идет сбор данных</h3>",$v['@attributes']['name']);
		    }
		    if ($v['type'] == 5 && $v['monitor'] == 1){
			//system
			$type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header);
			if (isset($v['system']['load']) && isset($v['system']['cpu']['user']) && isset($v['system']['cpu']['system'])){
			    $type[$key][]="<table>";
			    $type[$key][]="<thead>";
				$type[$key][]="<tr>";
				    $type[$key][]="<th>Load</th>";
				    $type[$key][]="<th>CPU</th>";
				    $type[$key][]="<th>Memory</th>";
				    $type[$key][]="<th>Swap</th>";
				$type[$key][]="</tr>";
			    $type[$key][]="</thead>";
			    $type[$key][]=sprintf("<tr class=\"center%s\">",$v['status'] > 0 ? " alarm" : "");
				    $type[$key][]=sprintf("<td>[%s]</td>",implode("] [",$v['system']['load']));
				    $type[$key][]=sprintf("<td>%s%%us,%s%%sy</td>",$v['system']['cpu']['user'],$v['system']['cpu']['system']);
				    $type[$key][]=sprintf("<td>%s%s</td>",isset($v['system']['memory']['percent']) ? sprintf("%s%%",$v['system']['memory']['percent']) : "n/a",(isset($v['system']['memory']['kilobyte']) && $v['system']['memory']['kilobyte'] > 0) ? sprintf(" [%s]",get_bwk($v['system']['memory']['kilobyte']*1024)) : "");
				    $type[$key][]=sprintf("<td>%s%s</td>",isset($v['system']['swap']['percent']) ? sprintf("%s%%",$v['system']['swap']['percent']) : "n/a",(isset($v['system']['swap']['kilobyte']) && $v['system']['swap']['kilobyte'] > 0) ? sprintf(" [%s]",get_bwk($v['system']['swap']['kilobyte']*1024)) : "");
			    $type[$key][]="</tr>";
			    $type[$key][]="</table>";
			}else{
			    $type[$key][]="Нет данных";
			}
		    }elseif ($v['type'] == 3 && $v['monitor'] == 1){
			//process
			$type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header);
			if (isset($v['cpu']) && isset($v['memory']) && isset($v['uptime'])){
			    $type[$key][]="<table>";
			    $type[$key][]="<thead>";
				$type[$key][]="<tr>";
				    $type[$key][]="<th>CPU</th>";
				    $type[$key][]="<th>Memory</th>";
				    $type[$key][]="<th>uptime</th>";
				    $type[$key][]="<th>PID</th>";
				    $type[$key][]="<th>UID</th>";
				    $type[$key][]="<th>GID</th>";
				    $type[$key][]="<th>threads</th>";
				    $type[$key][]="<th>children</th>";
				$type[$key][]="</tr>";
			    $type[$key][]="</thead>";
			    $type[$key][]=sprintf("<tr class=\"center%s\">",$v['status'] > 0 ? " alarm" : "");
				$type[$key][]=sprintf("<td>%s%%</td>",$v['cpu']['percenttotal'] > 0 ? $v['cpu']['percenttotal'] : 0);
				$type[$key][]=sprintf("<td>%s%%%s</td>",$v['memory']['percenttotal'] > 0 ? $v['memory']['percenttotal'] : 0,(isset($v['memory']['kilobytetotal']) && $v['memory']['kilobytetotal'] > 0) ? sprintf(" [%s]",get_bwk($v['memory']['kilobytetotal']*1024)) : "");
				$type[$key][]=sprintf("<td>%s</td>",uptime($v['uptime'],"string"));
				$type[$key][]=sprintf("<td>%s</td>",$v['pid']);
				$type[$key][]=sprintf("<td>%s</td>",$v['uid']);
				$type[$key][]=sprintf("<td>%s</td>",$v['gid']);
				$type[$key][]=sprintf("<td>%s</td>",$v['threads']);
				$type[$key][]=sprintf("<td>%s</td>",$v['children']);
			    $type[$key][]="</tr>";
			    $type[$key][]="</table>";

			    if (isset($v['port'])){
				$type[$key][]="<table>";
				$type[$key][]="<thead>";
				$type[$key][]="<tr>";
				    $type[$key][]="<th>Тип</th>";
				    $type[$key][]="<th>Хост</th>";
				    $type[$key][]="<th>Порт</th>";
				    $type[$key][]="<th>Протокол</th>";
				    $type[$key][]="<th>Время ответа</th>";
				$type[$key][]="</tr>";
				$type[$key][]="</thead>";
				if (!isset($v['port'][0])){
				    $ptmp=$v['port'];
				    $v['port']=array( '0' => $ptmp );
				    unset($ptmp);
				}
				foreach ($v['port'] as $kp=>$pv){
				    $class = "";
				    if ($v['status'] > 0){
					$class = " alarm";
					if (isset($pv['responsetime']) && $pv['responsetime'] > 0 ){
					    $class = "";
					}
				    }
				    $type[$key][]=sprintf("<tr class=\"center%s\">",$class);
					$type[$key][]="<td>Порт</td>";
					$type[$key][]=sprintf("<td>%s</td>",isset($pv['hostname']) ? $pv['hostname'] : "n/a");
					$type[$key][]=sprintf("<td>%s</td>",isset($pv['portnumber']) ? $pv['portnumber'] : "n/a");
					$type[$key][]=sprintf("<td>%s%s</td>",isset($pv['type']) ? $pv['type'] : "",isset($pv['protocol']) ? sprintf(" (%s)",$pv['protocol']) : "");
					$type[$key][]=sprintf("<td>%s</td>",(isset($pv['responsetime']) && $pv['responsetime'] > 0 ) ? $pv['responsetime'] : "n/a");
				    $type[$key][]="</tr>";
				}
				unset($class);
				$type[$key][]="</table>";
			    }
			}else{
			    $type[$key][]="Нет данных";
			}
		    }elseif ($v['type'] == 8 && $v['monitor'] == 1){
			//Network
			$type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header);
			$type[$key][]="<table>";
			$type[$key][]="<thead>";
			    $type[$key][]="<tr>";
				$type[$key][]="<th rowspan=2>Линк</th>";
				$type[$key][]="<th rowspan=2>Скорость</th>";
				$type[$key][]="<th colspan=3>Download</th>";
				$type[$key][]="<th colspan=3>Upload</th>";
			    $type[$key][]="</tr>";
			    $type[$key][]="<tr>";
				$type[$key][]="<th>Пакеты</th>";
				$type[$key][]="<th>Передано</th>";
				$type[$key][]="<th>Ошибки</th>";
				$type[$key][]="<th>Пакеты</th>";
				$type[$key][]="<th>Передано</th>";
				$type[$key][]="<th>Ошибки</th>";
			    $type[$key][]="</tr>";
			$type[$key][]="</thead>";
			$type[$key][]=sprintf("<tr class=\"center%s\">",$v['status'] > 0 ? " alarm" : "");
			    $type[$key][]=sprintf("<td>%s</td>",$v['link']['state'] == 1 ? "Да" : "Нет");
			    if ($v['link']['state'] == 1){
				$type[$key][]=sprintf("<td>%sMbit %s</td>",$v['link']['speed']/1000000,$v['link']['duplex'] == 1 ? "full-duplex" : "half-duplex");
				$type[$key][]=sprintf("<td>%s</td>",$v['link']['download']['packets']['total']);
				$type[$key][]=sprintf("<td>%s</td>",get_bwk($v['link']['download']['bytes']['total']));
				$type[$key][]=sprintf("<td%s>%s</td>",$v['link']['download']['errors']['total'] > 0 ? " class=warn" : "",$v['link']['download']['errors']['total']);
				$type[$key][]=sprintf("<td>%s</td>",$v['link']['upload']['packets']['total']);
				$type[$key][]=sprintf("<td>%s</td>",get_bwk($v['link']['upload']['bytes']['total']));
				$type[$key][]=sprintf("<td%s>%s</td>",$v['link']['upload']['errors']['total'] > 0 ? " class=warn" : "",$v['link']['upload']['errors']['total']);
			    }else{
				$type[$key][]="<td colspan=9>n/a</td>";
			    }
			$type[$key][]="</tr>";
			$type[$key][]="</table>";
			
			if (defined('NETWORK_ERRORS_AS_ALARM') && NETWORK_ERRORS_AS_ALARM === true){
			    if ($v['link']['upload']['errors']['total'] > 0 || $v['link']['download']['errors']['total'] > 0){
				$ret['alarm']++;
			    }
			}
		    }elseif ($v['type'] == 0 && $v['monitor'] == 1){
			//Filesystem
			$type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header);
			if ($v['status'] == 512){
			    $type[$key][]="<span class=\"alarm\">Раздела не существует</span>";
			}else{
			    $type[$key][]="<table>";
			    $type[$key][]="<thead>";
				$type[$key][]="<tr>";
				    $type[$key][]="<th>Тип</th>";
				    $type[$key][]="<th>Флаги</th>";
				    $type[$key][]="<th>Использовано</th>";
				    $type[$key][]="<th>Использовано inodes</th>";
				$type[$key][]="</tr>";
			    $type[$key][]="</thead>";
			    $type[$key][]=sprintf("<tr class=\"center%s\">",$v['status'] > 0 ? " alarm" : "");
				$type[$key][]=sprintf("<td>%s</td>",isset($v['fstype']) ? $v['fstype'] : "n/a");
				$type[$key][]=sprintf("<td>%s</td>",isset($v['fsflags']) ? (is_array($v['fsflags']) ? implode(", ",$v['fsflags']) : ($v['fsflags'] ? $v['fsflags'] : "нет") ) : "n/a");
				$type[$key][]=sprintf("<td>%s%s</td>",isset($v['block']['percent']) ? sprintf("%s%%",$v['block']['percent']) : "n/a",(isset($v['block']['usage']) && $v['block']['usage']> 0) ? sprintf(" [%s]",get_bwk($v['block']['usage']*1024*1024)) : "");
				$type[$key][]=sprintf("<td>%s%s</td>",isset($v['inode']['percent']) ? sprintf("%s%%",$v['inode']['percent']) : "n/a",(isset($v['inode']['usage']) && $v['inode']['usage'] > 0) ? sprintf(" [%s objects]",$v['inode']['usage']) : "");
				$type[$key][]="</tr>";
			    $type[$key][]="</table>";
			}
		    }elseif ($v['type'] == 2 && $v['monitor'] == 1){
			//File checks
			$type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header);
			if ($v['status'] == 512){
			    $type[$key][]="<span class=\"alarm\">Файла не существует</span>";
			}else{
			    $type[$key][]="<table>";
			    $type[$key][]="<thead>";
				$type[$key][]="<tr>";
				    $type[$key][]="<th rowspan=2>UID</th>";
				    $type[$key][]="<th rowspan=2>GID</th>";
				    $type[$key][]="<th rowspan=2>Mode</th>";
				    $type[$key][]="<th rowspan=2>Размер</th>";
				    $type[$key][]="<th rowspan=2>Checksum</th>";
				    $type[$key][]="<th colspan=3>Даты</th>";
			    $type[$key][]="</tr>";
				$type[$key][]="<tr>";
				    $type[$key][]="<th>access</th>";
				    $type[$key][]="<th>change</th>";
				    $type[$key][]="<th>modify</th>";
				$type[$key][]="</tr>";
				$type[$key][]="</thead>";
				$type[$key][]=sprintf("<tr class=\"center%s\">",$v['status'] > 0 ? " alarm" : "");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['uid']) ? (isset($sysUsers[$v['uid']]) ? $sysUsers[$v['uid']] : $v['uid']) : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['gid']) ? (isset($sysGroups[$v['gid']]) ? $sysGroups[$v['gid']] : $v['gid']) : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['mode']) ? $v['mode'] : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",(isset($v['size']) && $v['size'] > 0) ? $v['size'] : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",(isset($v['checksum']) && $v['checksum'] > 0) ? $v['checksum'] : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",(isset($v['timestamps']['access']) && $v['timestamps']['access'] > 0) ? date("d.m.Y H:i:s",$v['timestamps']['access']) : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",(isset($v['timestamps']['change']) && $v['timestamps']['change'] > 0) ? date("d.m.Y H:i:s",$v['timestamps']['change']) : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",(isset($v['timestamps']['modify']) && $v['timestamps']['modify'] > 0) ? date("d.m.Y H:i:s",$v['timestamps']['modify']) : "n/a");
				$type[$key][]="</tr>";
			    $type[$key][]="</table>";
			}
		    }elseif ($v['type'] == 1 && $v['monitor'] == 1){
			//Directory checks
			$type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header);
			if ($v['status'] == 512){
			    $type[$key][]="<span class=\"alarm\">Директории не существует</span>";
			}else{
			    $type[$key][]="<table>";
			    $type[$key][]="<thead>";
				$type[$key][]="<tr>";
				    $type[$key][]="<th rowspan=2>UID</th>";
				    $type[$key][]="<th rowspan=2>GID</th>";
				    $type[$key][]="<th rowspan=2>Mode</th>";
				    $type[$key][]="<th colspan=3>Даты</th>";
			    $type[$key][]="</tr>";
				$type[$key][]="<tr>";
				    $type[$key][]="<th>access</th>";
				    $type[$key][]="<th>change</th>";
				    $type[$key][]="<th>modify</th>";
				$type[$key][]="</tr>";
				$type[$key][]="</thead>";
				$type[$key][]=sprintf("<tr class=\"center%s\">",$v['status'] > 0 ? " alarm" : "");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['uid']) ? (isset($sysUsers[$v['uid']]) ? $sysUsers[$v['uid']] : $v['uid']) : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['gid']) ? (isset($sysGroups[$v['gid']]) ? $sysGroups[$v['gid']] : $v['gid']) : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['mode']) ? $v['mode'] : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['timestamps']['access']) ? date("d.m.Y H:i:s",$v['timestamps']['access']) : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['timestamps']['change']) ? date("d.m.Y H:i:s",$v['timestamps']['change']) : "n/a");
				    $type[$key][]=sprintf("<td>%s</td>",isset($v['timestamps']['modify']) ? date("d.m.Y H:i:s",$v['timestamps']['modify']) : "n/a");
				$type[$key][]="</tr>";
			    $type[$key][]="</table>";
			}
		    }elseif ($v['type'] == 4 && $v['monitor'] == 1){
			//ICMP and ports check
			$type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header);
			if (!isset($v['icmp']) && !isset($v['port'])){
			    $type[$key][]="Нет данных";
			}else{
				if (isset($v['icmp'])){
				    $type[$key][]="<table>";
				    $type[$key][]="<thead>";
					$type[$key][]="<tr>";
					    $type[$key][]="<th>Тип</th>";
					    $type[$key][]="<th>Время ответа</th>";
					$type[$key][]="</tr>";
				    $type[$key][]="</thead>";
				    $type[$key][]=sprintf("<tr class=\"center%s\">",$v['status'] > 0 ? " alarm" : "");
					$type[$key][]=sprintf("<td>%s</td>",isset($v['icmp']['type']) ? $v['icmp']['type'] : "n/a");
					$type[$key][]=sprintf("<td>%s</td>",(isset($v['icmp']['responsetime']) && $v['icmp']['responsetime'] > 0 ) ? $v['icmp']['responsetime'] : "n/a");
				    $type[$key][]="</tr>";
				    $type[$key][]="</table>";
				}
				if (isset($v['port'])){
				    $type[$key][]="<table>";
				    $type[$key][]="<thead>";
					$type[$key][]="<tr>";
					    $type[$key][]="<th>Тип</th>";
					    $type[$key][]="<th>Хост</th>";
					    $type[$key][]="<th>Порт</th>";
					    $type[$key][]="<th>Протокол</th>";
					    $type[$key][]="<th>Время ответа</th>";
					$type[$key][]="</tr>";
				    $type[$key][]="</thead>";
				    if (!isset($v['port'][0])){
					$ptmp=$v['port'];
					$v['port']=array( '0' => $ptmp );
					unset($ptmp);
				    }
				    foreach ($v['port'] as $kp=>$pv){
					$type[$key][]=sprintf("<tr class=\"center%s\">",$v['status'] > 0 ? " alarm" : "");
					    $type[$key][]="<td>Порт</td>";
					    $type[$key][]=sprintf("<td>%s</td>",isset($pv['hostname']) ? $pv['hostname'] : "n/a");
					    $type[$key][]=sprintf("<td>%s</td>",isset($pv['portnumber']) ? $pv['portnumber'] : "n/a");
					    $type[$key][]=sprintf("<td>%s%s</td>",isset($pv['type']) ? $pv['type'] : "",isset($pv['protocol']) ? sprintf(" (%s)",$pv['protocol']) : "");
					    $type[$key][]=sprintf("<td>%s</td>",(isset($pv['responsetime']) && $pv['responsetime'] > 0 ) ? $pv['responsetime'] : "n/a");
					$type[$key][]="</tr>";
				    }
				    $type[$key][]="</table>";
				}
			}
		    }elseif ($v['type'] == 7 && $v['monitor'] == 1){
			//scripts checks
			$type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header ? $status_2_header : " :: OK");
		    }else{
			if ($v['monitor'] == 1){
			    $type[$key][]=sprintf("<h3>%s%s</h3>",$v['@attributes']['name'],$status_2_header);
			    $type[$key][]=error(sprintf("Неизвестный тип данных: %d",$v['type']));
			}
		    }
		}else{
		    deb("TYPE NOT SET");
		    deb($v);
		}
	    }
	}
    }

    foreach ($type as $k=>$v){
	if (count($type[$k]) > 0){
	    $ret[$k] = implode("\n",$type[$k]);
	}
    }

 return $ret;
}

function get_bwk($bw,$num=0){
    $bwm=array("байт","Кб","Мб","Гб");
    $nn=0;
    while($bw >= 1024){
	$nn++;
	if($num == 0){
	    $num = 2;
	}
	$bw/=1024;
    }
 return sprintf("%.".$num."f %s",$bw,isset($bwm[$nn]) ? $bwm[$nn] : "");
}

function uptime( $sec, $what = ""){
    $ret = array(
        'days' => 0,
        'hours' => 0,
        'mins' => 0,
        'secs' => 0,
        'string' => '',
    );

    if( $ret['days'] = floor( $sec / 86400 ) ){
        $sec -= $ret['days'] * 86400;
        $ret['string'] = sprintf( "%d дн. ", $ret['days'] );
    }

    if( $ret['hours'] = floor( $sec / 3600 ) ){
        $sec -= $ret['hours'] * 3600;
    }

    if( $ret['mins'] = floor( $sec / 60 ) ){
        $sec -= $ret['mins'] * 60;
    }
    $ret['secs'] = $sec;

    $ret['string'] .= sprintf( "%02d:%02d:%02d", $ret['hours'], $ret['mins'], $ret['secs'] );

    if ($what == "string"){
	$ret = $ret['string'];
    }
    return $ret;
}

function check_version( ){
    $ret = array();
    $err = array();
    if (!defined('VERSION') || !VERSION){
	$err[]="Локальная версия неизвестна";
    }

    if (count($err) == 0){
	$host="mmonit-free.subnets.ru";
	$port="80";
	$get=sprintf("/check_version.php?version=%s",VERSION);
	$text=sprintf("<span class=\"new_version\"><a href=\"http://%s/\" target=\"blank\">Доступна новая версия ~VERSION~</a></span>",$host);

	if (defined('CHECK_4_NEW_VERSION') && CHECK_4_NEW_VERSION === true){
	    if (!isset($_SESSION['check_version'])){
		$data='';
		$sock = @stream_socket_client(sprintf("tcp://%s:%d",$host,$port), $errno, $errstr, 5, STREAM_CLIENT_CONNECT);
		if ($sock&&$errno==0){
		    @fwrite($sock, sprintf("GET %s HTTP/1.0\r\nHost: %s\r\nUser-Agent: mMonit version CHECKER v0.1\r\nAccept: */*\r\n\r\n",$get,$host));
		    while (!@feof($sock)) {
			$data.=@fgets($sock, 1024);
		    }
		    @fclose($sock);
		}
		if ($data){
		    $tmp=explode("\r\n\r\n",$data);
		    if (isset($tmp[1]) && check_version_number($tmp[1])){
			$ret['version']=$tmp[1];
			$_SESSION['check_version']=$tmp[1];
		    }
		}
		if (!isset($_SESSION['check_version'])){
		    $_SESSION['check_version'] = VERSION;
		}
	    }else{
		$tmp = check_version_number($_SESSION['check_version']);
		if ($tmp){
		    $ret['version']=$tmp;
		}
	    }
	    
	    if (isset($ret['version'])){
		$ret['version']=preg_replace("/~VERSION~/",$ret['version'],$text);
	    }
	}
    }

    if (count($err) > 0){
	$ret['error']=$err;
    }
 return $ret;
}

function check_version_number( $version ="" ){
    $ret="";
    if (preg_match("/^(\d{1,3})(\.\d{1,2}){0,2}$/",trim($version))){
	$my=preg_replace("/\./","",VERSION);
	$cur=preg_replace("/\./","",$version);
	if ( (int)$cur > (int)$my ){
	    $ret=$version;
	}
    }
 return $ret;
}

?>