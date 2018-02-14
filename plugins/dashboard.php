<?php
/*
    copyright (c) 2018 MEGA-NET.RU for SUBNETS.RU project (Moscow, Russia)
    Author: Nikolaev Dmitry <virus@subnets.ru>
*/
$pathinfo = dirname(__FILE__);
require_once(realpath(sprintf("%s/../func.php",$pathinfo)));
$location=preg_replace("/^\//","",$_SERVER['REQUEST_URI']);
global $services;
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
	$refresh = (int)REFRESH_PAGE;
	if ((int)REFRESH_PAGE <= 59){
	    $refresh=60;
	}
	$get_hosts=get_hosts();
	if (isset($get_hosts['error'])){
	    $err=$get_hosts['error'];
	}

	if (count($err) == 0){
	    $hosts_div=array();
	    printf("<h3>Найдено хостов: %d</h3>",count($get_hosts['hosts']));
	    print "<table id=\"hostsTBL\">";
	    print "<thead>";
		print "<tr>";
		    print "<th rowspan=2>#</th>";
		    print "<th rowspan=2>Хост</th>";
		    print "<th rowspan=2>Проблем</th>";
		    print "<th rowspan=2>Дата обновления</th>";
		    print "<th colspan=4>Описание хоста</th>";
		    print "<th colspan=3>Monit</th>";
		print "</tr>";
		print "<tr>";
		    print "<th>OS</th>";
		    print "<th>CPUs</th>";
		    print "<th>MEM</th>";
		    print "<th>Swap</th>";
		    print "<th>Интервал обновления</th>";
		    print "<th>Uptime</th>";
		    print "<th>Версия</th>";
		print "</tr>";
	    print "</thead>";
	    $nn=1;
	    foreach($get_hosts['hosts'] as $k=>$v){
		$data = isset($v['data']) ? $v['data'] : array();

		$class = "";
		$poll = isset($data['server']['poll']) ? (int)$data['server']['poll'] : 0;
		if (!$poll){
		    $class="gray";
		}elseif (time() > ($data['fdate']+$poll*2)){
		    $class="warn";
		    if (time() > ($data['fdate']+$poll*4)){
			$class="alarm";
		    }
		}
		if ( ( (int)$poll > REFRESH_PAGE ) && ( (int)$refresh > (int)$poll) ){
		    $refresh = $poll;
		}

		//Host detail div
		$host_div=array();
		$host_div[]=sprintf("<div id=\"host_%s\" class=\"hidden\">",$k);
		if (isset($v['error'])){
		    $host_div[]=error($v['error']);
		}else{
		    if (!$poll){
			$host_div[]=error("Таймер обновления данных отсутствует или равен нулю");
		    }elseif (time() > ($data['fdate']+$poll*2)){
			$tmp=sprintf("Хост не прислал данных в течение %s секунд",$poll*2);
			if (time() > ($data['fdate']+$poll*4)){
			    $tmp=sprintf("Хост не прислал данных в течение %s секунд",$poll*4);
			}
			$host_div[]=error($tmp);
			unset($tmp);
		    }
		    if(isset($data['services']) && is_array($data['services'])){
			$hs=parse_services($data['services']);
			foreach ($services as $sk=>$sv){
			    if (isset($hs[$sv['key']])){
				$host_div[]=sprintf("<h2>%s</h2>",$sv['name']);
				$host_div[]=$hs[$sv['key']];
				$host_div[]="<HR>";
			    }
			}
			if (is_developer()){
			    $host_div[]=sprintf("<BR><code class=\"developer\">DEBUG INFO:\n%s</code>",print_r($data,true));
			}
		    }else{
			$host_div[]=error("Нет данных по сервисам...");
			if (is_developer()){
			    $host_div[]=sprintf("<BR><HR><code class=\"developer\">DEBUG INFO:\n%s</code>",print_r($data,true));
			}
		    }
		}
		$host_div[]="</div>";
		$hosts_div[]=implode("\n",$host_div);
		//

		$host_url = "";
		if (isset($data['server']['httpd']) && is_array($data['server']['httpd'])){
		    $httpd = $data['server']['httpd'];
		    if (count($httpd) == 3){
			$host_url=sprintf("<a href=\"%s://%s%s%s:%d\" target=\"_blank\"><span class=\"icon-globus\"></span></a>",
			    (isset($httpd['ssl']) && $httpd['ssl']) ? "https" : "http",
			    (isset($data['server']['credentials']['username']) && $data['server']['credentials']['username']) ? $data['server']['credentials']['username'] : "",
			    (isset($data['server']['credentials']['password']) && $data['server']['credentials']['password']) ? ":".$data['server']['credentials']['password']."@" : "",
			    isset($httpd['address']) ? $httpd['address'] : "unknown",
			    isset($httpd['port']) ? $httpd['port'] : 0
			);
		    }
		}
		printf("<tr class=\"center%s\">",$class ? " ".$class : "");
		    printf("<td>%d</td>",$nn++);
		    $title=sprintf("ID хоста: %s",$k);
		    printf("<td id=\"hostTD_%s\" alt=\"%s\" title=\"%s\"><b><a class=\"click\" data-id=\"%s\">%s</a></b>%s</td>",$k,$title,$title,$k,isset($v['name']) ? $v['name'] : "отсутствует",$host_url ? " ".$host_url : "");
		    printf("<td%s>%s</td>",(isset($hs['alarm']) && $hs['alarm'] > 0) ? " class=warn" : "",(isset($hs['alarm']) && $hs['alarm'] >= 0) ? $hs['alarm'] : "n/a");
		    printf("<td>%s</td>",isset($data['fdate']) ? date("d.m.Y H:i:s",$data['fdate']) : "неизвестна");

		    printf("<td>%s %s %s</td>",isset($data['platform']['name']) ? $data['platform']['name'] : "n/a",isset($data['platform']['release']) ? $data['platform']['release'] : "",isset($data['platform']['machine']) ? $data['platform']['machine'] : "");
		    printf("<td>%s</td>",isset($data['platform']['cpu']) ? $data['platform']['cpu'] : "n/a");
		    printf("<td>%s</td>",(isset($data['platform']['memory']) && $data['platform']['memory'] > 0) ? get_bwk($data['platform']['memory']*1024) : "n/a");
		    printf("<td>%s</td>",(isset($data['platform']['swap']) && $data['platform']['swap'] > 0) ? get_bwk($data['platform']['swap']*1024) : "n/a");
		    
		    printf("<td>%s</td>",$poll ? uptime($poll,"string") : "n/a");
		    $title=sprintf("Дата старта monit: %s",isset($data['host_header']['incarnation']) ? date("d.m.Y H:i:s",$data['host_header']['incarnation']) : "неизвестна");
		    printf("<td alt=\"%s\" title=\"%s\">%s</td>",$title,$title,(isset($data['server']['uptime']) && $data['server']['uptime'] > 0) ? uptime($data['server']['uptime'],"string") : "n/a");
		    printf("<td>%s</td>",isset($data['host_header']['version']) ? $data['host_header']['version'] : "n/a");
		print "</tr>";

	    }
	    print "</table>";
	    print implode("\n",$hosts_div);
	    
	    //deb($get_hosts);
?>
	    <script>
	    window.addEvent( 'domready', function( ) {
		Array.each( $$('#hostsTBL a.click'), function( v, i ){
		    v.addEvent('click',function( el ){
			$('modalHeader').set('html',v.get('text') + ' <small>(' + v.get('data-id') + ')</small>');
			$('modalContent').set('html',$('host_'+v.get('data-id')).get('html'));
			MONIT.openModal();
		    });
		});
	    });
<?php 
	    printf("refresh_timer = %d;\n",$refresh*1000);
	    printf("default_timer = %d;\n",REFRESH_PAGE*1000);
	    printf("page = '%s';\n",$location);
?>
	    if (default_timer > refresh_timer){
		refresh_timer = default_timer;
	    }
	    MONIT.console({ level: 'info', text: 'Refresh page timer ' + refresh_timer/1000 +' seconds'});
	    setTimeout(function(){
		MONIT.console({ level: 'info', text: 'Starting page refresh' });
		MONIT.mJson({
		    id: 'workArea',
		    location: page,
		});
	    },refresh_timer);
	    </script>
<?php
	}

	if (count($err) > 0){
	    print error($err);
	}
    }
}

?>