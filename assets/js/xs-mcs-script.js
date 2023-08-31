jQuery(document).ready(function($){
	'use strict';
	$(document).on('click', '.xs-mcs-curr-switcher-wrap .xs-mcs-selected-curr', function(e){
		e.preventDefault();
		if($(this).next().attr('area-expanded') == 'true'){
			$(this).next().attr('area-expanded', 'false');
			$(this).next().slideUp();
		}else{
			$(this).next().attr('area-expanded', 'true');
			$(this).next().slideDown();
		}
	}).on('click', '.xs-mcs-curr-switcher-wrap .xs-mcs-currencies a', function(e){
		e.preventDefault();
		var clicked_ele = $(this);
		var loader = '<div class="xs-mcs-loader"><span class="xs-mcs-moving-dot"></span></div>';
		$(loader).insertAfter( $(this).closest('.xs-mcs-curr-switcher-wrap').find('.xs-mcs-selected-curr span') );
		
		$.ajax({
			url 	: xs_mcs_object.ajaxurl,
			data	: { 'action' : 'xs_mcs_switch_currency', 'currency' : clicked_ele.attr('data-curr') },
			type	: 'post',
			success	: function(res){
				var old_cur = $('.xs-mcs-selected-curr').html();
				old_cur = old_cur.split('<');
				old_cur[0].toLowerCase();
				
				$('.xs-mcs-selected-curr').html( clicked_ele.html() );
				$('a[data-curr="'+clicked_ele.attr('data-curr')+'"]').remove();
				$('<a data-curr="'+old_cur[0].toLowerCase()+'">'+old_cur[0]+' <'+old_cur[1]+'</a>').insertBefore ($('.xs-mcs-currencies a:first-child'));
				
				var d = new Date();
				d.setTime(d.getTime() + (30*24*60*60*1000));
				var expires = "expires="+ d.toUTCString();
				document.cookie = "xs_mcs_switch_currency=" + res.currency + ";" + expires + ";path=/";
				xs_mcs_refresh_fragments(res);
			},
		}).always(function(a, b, c){
			if(b == 'error'){
				$('.xs-mcs-loader').remove();
				alert(c)
			}
		});
		
	});
	
	
	function xs_mcs_refresh_fragments(res){
		var url = woocommerce_params.wc_ajax_url;
		url = url.replace("%%endpoint%%", "get_refreshed_fragments");
		$.post(url, function(data, status){
			if ( data.fragments ){
				jQuery.each(data.fragments, function(key, value){
					jQuery(key).replaceWith(value);
				});
			}
			jQuery('body').trigger( 'wc_fragments_refreshed' );
			if(res.currency){
				var current_url = window.location.href;
				if(current_url.indexOf('?') > -1){
					window.location.href = current_url+'&currency='+res.currency;
				}else{
					window.location.href = current_url+'?currency='+res.currency;
				}
			}
		});
	}
	xs_mcs_refresh_fragments({'currency':0});
	var xs_mcs_old_currency = xs_mcs_getCookie('xs_mcs_switch_currency');
	setInterval( function(){
		var xs_mcs_new_currency = xs_mcs_getCookie('xs_mcs_switch_currency');
		if( xs_mcs_old_currency != xs_mcs_new_currency ){
			xs_mcs_old_currency = xs_mcs_new_currency;
			var d = new Date();
			d.setTime( d.getTime() + (30*24*60*60*1000) );
			var expires = "expires="+ d.toUTCString();
			document.cookie = "xs_mcs_switch_currency=" + xs_mcs_new_currency + ";" + expires + ";path=/";
			xs_mcs_refresh_fragments({'currency':0});
		}
	}, 3000 );
	
	function xs_mcs_getCookie(cname) {
		var name = cname + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		for(var i = 0; i <ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
			  c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
			  return c.substring(name.length, c.length);
			}
		}
		return "";
	}
});