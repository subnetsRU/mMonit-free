/*
	main js functions

        Copyright (c) Aug 2017 MEGA-NET.RU (Moscow, Russia)
        Authors: Panfilov Alexey, Nikolaev Dmitry
*/
<?php
    require_once("../func.php");
    header( "Content-Type:application/javascript" );

    //set`s mootools locale
    print "
    if (typeof Locale != 'undefined'){
	Locale.use('ru-RU');
    }\n";
    //
    printf("HOST = '%s';\n",HOST);
    printf("URL = '%s';\n",URL);
?>

(function (window) {
	window.MONIT||(window.MONIT = {});
	MONIT.url = HOST;
	MONIT.mJson = function( obj ){
	    if (!MONIT.is_null(obj.request)){
		obj.id = 'workArea';
	    }
	    if (MONIT.is_null(obj.request)){
		obj.request = '';
	    }
	    if (!MONIT.is_null(obj.location)){
		new Request.HTML( {
		    url: URL+obj.location,
		    update: obj.id,
		    onRequest: function( ){
			if( obj.img !== false && !MONIT.is_null($(obj.id))){
			    $( obj.id ).empty( ).adopt(
				new Element( 'span', {
				    class: 'icon-loader',
				} )
			    );
			}
		    },
		    onSuccess: function( ){
			if (typeof obj.onSuccess === 'function'){
			    obj.onSuccess();
			}
		    },
		    onFailure: function( err ){
			var error = 'Загрузка '+obj.location+' не удалась: '+err.status + ' '+err.statusText;
			MONIT.console({
			    level: 'error',
			    text: error,
			    informer: true,
			    obj: err,
			});
			$( obj.id ).empty( ).adopt(
			    new Element( 'div', {
				class: 'error',
				text: error,
			    } )
			);
			if (typeof obj.onError === 'function'){
			    obj.onError();
			}
		    }
		} ).post( 'data='+MONIT.base64_encode(obj.request) );
	    }else{
		var error = 'Запрашиваемый URL неизвестен';
		MONIT.console({
		    level: 'error',
		    text: error,
		    informer: true,
		    obj: obj,
		});
		$( obj.id ).empty( ).adopt(
		    new Element( 'div', {
			class: 'error',
			text: error,
		    } )
		);
	    }
	}

	MONIT.is_null = function( obj ){
		if ( obj === undefined ){
	    	    return 1;
		}else if ( obj === null ){
	    	    return 1;
		}else if ( obj == 0 ){
	    	    return 1;
		}else if ( obj == '' ){
	    	    return 1;
		}
		return 0;
	}

	MONIT.console = function( obj ){
	    var options = {
		level: 'error',
		text: 'no text',
		informer: false,
		obj: '',
	    }
	    if( !MONIT.is_null( obj ) && typeof obj == 'object' ){
		if( !MONIT.is_null( obj.level ) ){
		    options.level = obj.level;
		}
		if( !MONIT.is_null( obj.text ) ){
		    options.text = obj.text;
		}
		if( !MONIT.is_null( obj.informer ) ){
		    options.informer = obj.informer;
		}
		if( !MONIT.is_null( obj.obj ) ){
		    options.obj = obj.obj;
		}
	    }
	    if( options.level == 'error' ){
		console.error( options.text, options.obj );
	    }else if( options.level == 'warn' ){
		console.warn( options.text, options.obj );
	    }else if( options.level == 'info' ){
		console.info( options.text, options.obj );
	    }else{
		console.log( options.text, options.obj );
	    }
	    if( !MONIT.is_null( options.informer ) ){
		if( options.level == 'error' ){
		    MONIT.show_info( '2', options.text );
		}else{
		    MONIT.show_info( '1', options.text );
		}
	    }
	}

	MONIT.show_info = function( type, text ){
	    $( 'informer' ).empty( ).timer( );		//Clear old informer text
	    var set_class='informer_unknown';	
	    if( type == 1 ){
		set_class = 'informer_good';
	    }else if( type == 2 ){
		set_class = 'informer_bad';
	    }

	    $( 'informer' ).adopt( new Element( 'div#informermess', {
		html: decodeURIComponent( text ),
		class: 'informer_general ' + set_class,
	    } ) );
	    if (MONIT.is_null($('body').retrieve('scroll'))){
		$('body').store('scroll','0px');
	    }
	    $( 'informer' ).setStyles({position: 'absolute',top: $('body').retrieve('scroll')});
	    $( 'informer' ).removeClass( 'hidden' );
	    $( 'informer' ).addClass( 'visible' );

	    $( 'informer' ).timer( {
		value: 5000,
		callback: function( ){
		    $( 'informer' ).removeClass( 'visible' );
		    $( 'informer' ).addClass( 'hidden' );
		    $( 'informer' ).empty( );
		}
	    } );
	}

	MONIT.base64_encode = function( data ){
	    return btoa( data );
	}

	MONIT.base64_decode = function( data ){
	    return atob( data );
	}

 	MONIT.base64 = {
	    _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	    encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = MONIT.base64._utf8_encode(input);

		while (i < input.length) {

    		    chr1 = input.charCodeAt(i++);
    		    chr2 = input.charCodeAt(i++);
    		    chr3 = input.charCodeAt(i++);

    		    enc1 = chr1 >> 2;
    		    enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
    		    enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
    		    enc4 = chr3 & 63;

    		    if (isNaN(chr2)) {
        		enc3 = enc4 = 64;
    		    } else if (isNaN(chr3)) {
        		enc4 = 64;
    		    }

    		    output = output +
    			MONIT.base64._keyStr.charAt(enc1) + MONIT.base64._keyStr.charAt(enc2) +
    			MONIT.base64._keyStr.charAt(enc3) + MONIT.base64._keyStr.charAt(enc4);

		}
		return output;
	    },
	    decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
	        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		while (i < input.length) {
    		    enc1 = MONIT.base64._keyStr.indexOf(input.charAt(i++));
    		    enc2 = MONIT.base64._keyStr.indexOf(input.charAt(i++));
    		    enc3 = MONIT.base64._keyStr.indexOf(input.charAt(i++));
    		    enc4 = MONIT.base64._keyStr.indexOf(input.charAt(i++));

    		    chr1 = (enc1 << 2) | (enc2 >> 4);
    		    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
    		    chr3 = ((enc3 & 3) << 6) | enc4;
    		    output = output + String.fromCharCode(chr1);
    		    if (enc3 != 64) {
        		output = output + String.fromCharCode(chr2);
    		    }
    		    if (enc4 != 64) {
        		output = output + String.fromCharCode(chr3);
    		    }
		}
		output = MONIT.base64._utf8_decode(output);
		return output;
	    },
	    _utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
    		    var c = string.charCodeAt(n);
    		    if (c < 128) {
        		utftext += String.fromCharCode(c);
    		    }else if((c > 127) && (c < 2048)) {
        		utftext += String.fromCharCode((c >> 6) | 192);
        		utftext += String.fromCharCode((c & 63) | 128);
    		    }else {
        		utftext += String.fromCharCode((c >> 12) | 224);
        		utftext += String.fromCharCode(((c >> 6) & 63) | 128);
        		utftext += String.fromCharCode((c & 63) | 128);
    		    }
		}
		return utftext;
	    },
	    _utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while ( i < utftext.length ) {
		    c = utftext.charCodeAt(i);
		    if (c < 128) {
        		string += String.fromCharCode(c);
        		i++;
		    }else if((c > 191) && (c < 224)) {
        		c2 = utftext.charCodeAt(i+1);
        		string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
        		i += 2;
		    }else {
        		c2 = utftext.charCodeAt(i+1);
        		c3 = utftext.charCodeAt(i+2);
        		string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
        		i += 3;
		    }
		}
		return string;
	    }
	}

	MONIT.addMenuClick = function( obj ){
	    if (!MONIT.is_null(obj.id)){
		Array.each($$('#'+obj.id+' li'), function( v, i ){
		    if (v.hasClass('menuBtn')){
			v.removeEvent( 'click', MONIT.menuClick );
			v.addEvent( 'click', MONIT.menuClick );
		    }
		});
	    }
	    return false;
	}

	MONIT.menuClick = function( el ){
	    if (!MONIT.is_null($('customMessage'))){
		$('customMessage').empty();
	    }
	    el.preventDefault();	//Prevent default action: go to URL
	    var ul = el.target.getParent('ul');
	    Array.each($$('#'+ul.id+' li a'), function( v, i ){
		v.removeClass('active');
	    });
	    if (!MONIT.is_null(this.getChildren('a')[0])){
		this.getChildren('a')[0].addClass('active');
	    }
	    if(!MONIT.is_null(this.getChildren('a')[0])){
		var request, location = '';
		if (!MONIT.is_null(this.getChildren('a')[0]) && !MONIT.is_null(this.getChildren('a')[0].href.split('?')[1])){
		    location = this.getChildren('a')[0].href.split('?')[1].replace('location=','');
		}
		if (!MONIT.is_null(this.getChildren('a')[0].get('data-request'))){
		    if (!MONIT.is_null(request)){
			request += '&';
		    }
		    request += this.getChildren('a')[0].get('data-request');
		}
		MONIT.mJson({
		    id: MONIT.is_null(ul.get('data-update')) ? 'workArea' : ul.get('data-update'),
		    location: location,
		    request: request,
		});
	    }
	    return false;
	}

	MONIT.submitButtonDelay = function( id ){
	    if (!MONIT.is_null(id)){
		$(id).disabled=true;
		setTimeout(function(){$(id).disabled=false;},2000);
	    }
	}
}(this));

if( typeof Element.prototype.timer === "undefined" ){
    Element.prototype.timer = function( arg ){
	var self = this;
	if( arg === undefined ){
	    clearTimer( );
	}else if( typeof arg == 'object' ){
	    if( /^\d+$/.test( arg.value ) ){
		if( arg.value === undefined || arg.value == 0 ){
		    if( self.retrieve( 'timeoutId' ) !== undefined ){
			clearTimer( );
		    }
		}else{
		    if( typeof arg.callback == 'function' ){
			if( self.retrieve( 'timeoutId' ) !== undefined ){
			    clearTimer( );
			}
			self.getParent( ).addEventListener( 'DOMNodeRemoved', onElementDestroy, false );
			if( arg.debug ){
			    console.log( 'Set timeout: ' + arg.callback + '): ' + arg.value );
			}
			self.store( 'timeoutId', setTimeout( function( ){
			    clearTimer( );
			    arg.callback( arg.arg );
			}, arg.value ) );
		    }
		}
	    }
	}

	function clearTimer( ){
	    if( self.retrieve( 'timeoutId' ) !== null ){
		clearTimeout( self.retrieve( 'timeoutId' ) );
	    }
	    if( self.getParent( ) ){
    		self.getParent( ).removeEventListener( 'DOMNodeRemoved', onElementDestroy, false );
	    }
	    self.eliminate( 'timeoutId' );
	}

	function onElementDestroy( e ){
	    if( arg.debug ){
		console.log( 'Destroy: ', e );
	    }
	    if( e.target.id == self.id ){
		if( arg.debug ){
		    console.log( 'clear timer for self: id ' + e.target.id );
		}
		clearTimer( self );
	    }
	}
	return self;
    }
}
