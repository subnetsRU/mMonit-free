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
		    print "<th rowspan=2>Дата обновления</th>";
		    print "<th colspan=6>Описание</th>";
		    print "<th rowspan=2>Версия monit</th>";
		print "</tr>";
		print "<tr>";
		    print "<th>Poll</th>";
		    print "<th>Uptime</th>";
		    print "<th>OS</th>";
		    print "<th>CPUs</th>";
		    print "<th>MEM</th>";
		    print "<th>Swap</th>";
		print "</tr>";
	    print "</thead>";
	    $nn=1;
	    foreach($get_hosts['hosts'] as $k=>$v){
		$data = isset($v['data']) ? $v['data'] : array();

		//Host detail div
		$host_div=array();
		$host_div[]=sprintf("<div id=\"host_%s\" class=\"hidden\">",$k);
		if (isset($v['error'])){
		    $host_div[]=error($v['error']);
		}else{
		    if(isset($data['services']) && is_array($data['services'])){
			$hs=parse_services($data['services']);
			foreach ($services as $sk=>$sv){
			    if (isset($hs[$sv['key']])){
				$host_div[]=sprintf("<h2>%s</h2>",$sv['name']);
				$host_div[]=$hs[$sv['key']];
				$host_div[]="<HR>";
			    }
			}
		    }else{
			$host_div[]="Нет данных...";
		    }
		}
		$host_div[]="</div>";
		$hosts_div[]=implode("\n",$host_div);
		//

		$class = "";
		$poll = isset($data['server']['poll']) ? $data['server']['poll'] : 0;
		if (!$poll){
		    $class="gray";
		}elseif (time() > ($data['fdate']+$poll*2)){
		    $class="warn";
		    if (time() > ($data['fdate']+$poll*4)){
			$class="alarm";
		    }
		}
		$host_url = "";
		if (isset($data['server']['httpd']) && is_array($data['server']['httpd'])){
		    $httpd = $data['server']['httpd'];
		    if (count($httpd) == 3){
			$host_url=sprintf("<a href=\"%s://%s:%d\" target=\"_blank\"><span class=\"icon-globus\"></span></a>",(isset($httpd['ssl']) && $httpd['ssl']) ? "https" : "http",isset($httpd['address']) ? $httpd['address'] : "unknown", isset($httpd['port']) ? $httpd['port'] : 0);
		    }
		}
		printf("<tr class=\"center%s\">",$class ? " ".$class : "");
		    printf("<td>%d</td>",$nn++);
		    $title=sprintf("ID хоста: %s",$k);
		    printf("<td id=\"hostTD_%s\" alt=\"%s\" title=\"%s\"%s><b><a class=\"click\" data-id=\"%s\">%s</a></b>%s</td>",$k,$title,$title,isset($hs['alarm']) ? " class=warn" : "",$k,isset($v['name']) ? $v['name'] : "отсутствует",$host_url ? " ".$host_url : "");
		    $title=sprintf("Дата старта monit: %s",isset($data['host_header']['incarnation']) ? date("d.m.Y H:i:s",$data['host_header']['incarnation']) : "неизвестна");
		    printf("<td alt=\"%s\" title=\"%s\">%s</td>",$title,$title,isset($data['fdate']) ? date("d.m.Y H:i:s",$data['fdate']) : "неизвестна");

		    printf("<td>%s сек.</td>",$poll ? $poll : "n/a");
		    printf("<td>%s</td>",isset($data['server']['uptime']) ? $data['server']['uptime'] : "n/a");
		    printf("<td>%s %s %s</td>",isset($data['platform']['name']) ? $data['platform']['name'] : "n/a",isset($data['platform']['release']) ? $data['platform']['release'] : "",isset($data['platform']['machine']) ? $data['platform']['machine'] : "");
		    printf("<td>%s</td>",isset($data['platform']['cpu']) ? $data['platform']['cpu'] : "n/a");
		    printf("<td>%s</td>",isset($data['platform']['memory']) ? $data['platform']['memory'] : "n/a");
		    printf("<td>%s</td>",isset($data['platform']['swap']) ? $data['platform']['swap'] : "n/a");
		    
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
	    })
	    </script>
<?php
	}

	if (count($err) > 0){
	    print error($err);
	}
    }
}

?>