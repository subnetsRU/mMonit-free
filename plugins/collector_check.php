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
    $check=collector_check_4_errors();
    //deb($check);
    if ($check){
	print "\n<div id=\"collector_errors\" class=\"hidden\">";
	    print "<div class=\"error\" style=\"width: 95%; overflow: auto;\">";
		print "<pre>";
		    print $check;
		print "</pre>";
	    print "</div>";
	print "</div>\n";

	$tmp=explode("\n",$check);
	printf("<span id=\"collector_show_errors\" class=\"click alarm\">Обнаружены ошибки при сборе данных [%d]</span>",count($tmp)-1);
	unset($tmp);

	print "\n<script>
	$('collector_show_errors').addEvent('click',function(){
	    $('modalHeader').set('html','Ошибки при сборе данных');
	    $('modalContent').set('html',$('collector_errors').get('html'));
	    MONIT.openModal();
	});
	</script>\n";
    }
}

?>