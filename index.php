<?php
/*
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
require_once("func.php");
print head();

$location="dashboard.php";
if (!chk_auth()){
    $location="login.php";
}
print "\n<div id=\"content\">";
    print "<!-- header start -->";
    print "<div id=\"header\">";
	print "<div id=\"informer\" class=\"informer hidden\"></div>";
	printf("<span id=\"version\">%s%s</span>",(defined('SYSTEM_NAME') && SYSTEM_NAME) ? SYSTEM_NAME : "",(defined('VERSION') && VERSION) ? sprintf(" v%s",VERSION) : "");
	printf("<span class=\"copyright\">&copy; <a href=\"http://www.mega-net.ru\" target=\"_blank\">Меганет-2003</a>, %s%s</span>",date("Y",time()) !="2018" ? "2018-": "",date("Y",time()));
    print "</div>";
    print "<!-- header end -->";

    print "\n<!-- menu start -->\n";
    print "<div id=\"mainMenu\">";
	print "<ul id=\"mainMenuList\" class=\"tabs\">";
	$menu=menu();
	if (is_array($menu) && count($menu) > 0){
	    foreach ($menu as $v){
		printf("<li class=\"menuBtn %s\"><a class=\"menu\" %shref=\"%sindex.php?location=%s\"><span>%s</span></a></li>",$v['class'],(isset($directSCI)&&$directSCI==$v['class_id']) ? "class=\"active\"" : "",URL,$v['location'],$v['text']);
	    }
	}
	print "</ul>";
    print "</div>";
    print "<!-- menu end -->";

    print "<!-- center start -->";
    print "<div id=\"centerArea\">";
	print "<div id=\"workArea\"></div>";
    print "</div>";
    print "<!-- center end -->";
    print "<div id=\"bottom\"></div>";
print "</div>";

print "<div id=\"modalDiv\" class=\"modal\">";
    print "<div id=\"modal\" class=\"modal-window hidden\">";
	print "<div id=\"modalHeader\" class=\"modal-header\">Заголовок</div>";
	print "<div id=\"modalContent\" class=\"modal-content\"></div>";
	print "<span id=\"modalClose\" class=\"modal-close\" alt=\"Закрыть\" title=\"Закрыть\" ></span>";
//		styles: windowStyles,
print "</div>";

print "\n<script>\n";
print "window.addEvent( 'domready', function( ) {\n";
    print "
    MONIT.mJson({
	id: 'workArea',
	location: 'plugins/".$location."',
    });\n";
    if (chk_auth()){
	print "MONIT.addMenuClick( { id: 'mainMenu' } );\n";
    }
    print "
    $('modalClose').addEvent('click',function(){
	MONIT.closeModal();
    });
    ";
print "});\n";
print "window.addEvent('keydown',function( e ){
    if (!MONIT.is_null(e.key) && e.key == 'esc'){
	MONIT.closeModal();
    }
});\n";
print "</script>\n";

print foot();

?>