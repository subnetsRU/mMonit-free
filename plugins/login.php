<?php
/*
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
$pathinfo = dirname(__FILE__);
require_once(realpath(sprintf("%s/../func.php",$pathinfo)));
$location=preg_replace("/^\//","",$_SERVER['REQUEST_URI']);
//deb("Request: ".print_r($param,true));

if (isset($param['data']) && $param['data']){
    $err=array();
    $request_params=get_request_params($param['data']);
    if (isset($request_params['error'])){
	$err=$request_params['error'];
    }else{
	$param = $request_params['data'];
	$param['location'] = $location;
    }

    //deb($param);

    if (count($err) == 0){
	if (!defined('AUTH') || !AUTH){
	    $err[]="Невозможно невозможно выполнить проверку данных";
	}
	if (!isset($param['act']) || !$param['act']){
	    $err[]="Действие неизвестно";
	}
    }

    if (isset($param['act'])){
	if ($param['act'] =="auth"){
	    if (!isset($param['data']) || AUTH != $param['data']){
		$err[]="Неправильный логин или пароль";
	    }else{
		$_SESSION['auth'] = "1";
		print "<h1>Добро пожаловать</h1>";
		login_logedin();
	    }
	}else{
	    $err[]=sprintf("Действие%s не найдено",is_developer() ? " ".$param['act'] : "");
	}
    }
    
    if (count($err) > 0){
	print error($err);
	unset($param['act']);
	print goback($param,array("update"=>"workArea"));
    }
}else{
    if (isset($_SESSION['auth']) && $_SESSION['auth'] == 1){
	login_logedin();
    }else{
	print "<table id=\"tbl_login\" class=\"tbl_login\">";
	    print "<tr>";
		print "<th class=head>Логин</th>";
		print "<th><input type=\"text\" id=\"login\" size=\"32\" maxlength=\"255\" placeholder=\"Введите Ваш логин\"></th>";
	    print "</tr>";
	print "<tr>";
	    print "<th class=head>Пароль</th>";
	    print "<th><input type=\"password\" id=\"pass\" size=\"32\" maxlength=\"32\" placeholder=\"Введите Ваш пароль\"></th>";
	print "</tr>";
	print "<tr>";
	    print "<td colspan=2 align=\"center\">";
		print "<div id=\"enterByLoginBtn\" class=\"button-main button-save button-rad\">Войти</div>";
	    print "</td>";
	print "</tr>";
	print "</table>";

	print "<noscript>";
	print "<div class=\"display-center\">
	    <center>
		<font color=\"red\"><b>Для работы в системе необходима поддержка Javascript.<BR>
		Ваш браузер не поддерживает Javascript или поддержка Javascript отключена в его настройках.</b></font>
	    </center>
	</div>";
	print "</noscript>";

	print "\n<script>
	    window.addEvent( 'domready', function( ) {
		    $('login').focus();
		    $('enterByLoginBtn').addEvent('click',function(){
			gm5();
		    });
		    $('pass').addEvent('keyup', function(e){
			if(e.key == \"enter\") {
			    gm5();
			}
		    });
	    });\n";
	    print "function gm5(){
		    if (!MONIT.is_null($('login').value) && !MONIT.is_null($('pass').value)){
			MONIT.mJson({
			    id: 'workArea',
			    location: '".$location."',
			    request: 'act=auth&data='+hex_md5($('login').value.toString()+$('pass').value.toString()),
			});
		    }else{
			if (MONIT.is_null($('login').value) && $('pass').value){
			    $('login').addClass('required');
			    MONIT.show_info('2','Введите логин');
			}else if ($('login').value && MONIT.is_null($('pass').value)){
			    $('pass').addClass('required');
			    MONIT.show_info('2','Введите пароль');
			}else{
			    $('login').addClass('required');
			    $('pass').addClass('required');
			    MONIT.show_info('2','Введите логин и пароль');
			}
		    }
	    }\n";
	    include(sprintf("%s/js/md5.js",LOC));
	print "</script>\n";
    }
}

function login_logedin(){
    printf("\n<script>setTimeout(function(){window.location.href='%s';},'1000');</script>\n",URL);
}
?>