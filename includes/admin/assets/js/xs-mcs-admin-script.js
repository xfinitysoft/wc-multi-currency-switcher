jQuery(document).ready(function($){
	'use strict';
	/* Currency Form Javascript starts */
	$('select[name="currency[name]"]').select2({
		'placeholder' : 'Select Currency',
		'width' : '280px'
	});
	
	$('#xs-mcs-get-value-btn').on('click', function(e){
		e.preventDefault;
		$('.xs-mcs-error').remove();
		if( $('select[name="currency[name]"]').val() ){
			$('#xs-mcs-get-value-btn').parent('td').find('.xs-mcs-spinner').css('display', 'inline-block');
			$.ajax({
				url 	: xsmcs.ajax_url,
				data	: { 'action' : 'xs_mcs_get_currency_value', 'currency_name' : $('select[name="currency[name]"]').val() },
				type	: 'post',
				success	: function(res){
					if(res.success){
						$('input[name="currency[value]"]').val(res.value);
					}else{
						$('<p class="xs-mcs-error">'+res.msg+'</p>').insertAfter($('#xs-mcs-get-value-btn'));
						$('input[name="currency[value]"]').val(res.value);
					}
				},
			}).always(function(a, b, c){
				if(b == 'error'){
					alert(c)
				}
				$('.xs-mcs-spinner').css('display', 'none');
			});
		}else{
			$('<p class="xs-mcs-error">Please select currency.</p>').insertAfter($('select[name="currency[name]"]'));
			return false;
		}
	});
	
	$('#xs-mcs-get-value-inc_ex-fee-btn').on('click', function(e){
		e.preventDefault;
		$('.xs-mcs-calculated-value').remove();
		if( $('select[name="currency[name]"]').val() ){
			$('.xs-mcs-calculated-value').parent('td').find('.xs-mcs-spinner').css('display', 'inline-block');
			$.ajax({
				url 	: 	xsmcs.ajax_url,
				data	: { 
					'action' 			: 'xs_mcs_get_currency_value_inc_exc_fee',
					'currency_name' 	: $('select[name="currency[name]"]').val(),
					'value'				: $('input[name="currency[value]"]').val(),
					'exchange_fee_type' : $('select[name="currency[exchange_fee][type]"]').val(),
					'exchange_fee'		: $('input[name="currency[exchange_fee][value]"]').val()
				},
				type	: 'post',
				success	: function(res){
					$('<p class="xs-mcs-calculated-value">'+res+'</p>').insertAfter($('#xs-mcs-get-value-inc_ex-fee-btn'));
				},
			}).always(function(a, b, c){
				if(b == 'error'){
					alert(c)
				}
				$('.xs-mcs-spinner').css('display', 'none');
			});
		}else{
			$('<p class="xs-mcs-error">Please select currency.</p>').insertAfter($('select[name="currency[name]"]'));
			return false;
		}
	});
	
	$('select[name="currency[name]"]').on('change', function(){
		if( $(this).val() ){
			$('.xs-mcs-target-curr-txt').text($(this).val());
		}else{
			$('.xs-mcs-target-curr-txt').text('');
		}
	});
	/* Currecny form Javascript ends here */
	
	/* Currecny page Javascript starts here */
	var $ = jQuery;
	$('.xs-mcs-delete-currency').on('click', function(e){
		e.preventDefault();
		if(window.confirm('Are you sure you want to delete this currency ?')){
			window.location.href = $(this).attr('href');
		}
		return false;
	});
	/* Currecny page Javascript ends here */
	
	
	/* Options page Javascript starts here */
	$('.xs-mcs-options-form .xs-mcs-submit input[type="submit"]').on('click', function(e){
		e.preventDefault();
		$('.xs-mcs-error, .xs-mcs-success').remove();
		var submit_btn = $(this);
		
		$(this).parent('.xs-mcs-submit').find('.xs-mcs-spinner').css('display', 'inline-block');
		$.post(
			xsmcs.ajax_url,
			$('.xs-mcs-options-form').serialize(),
			function(res){
				if( !res.success ){
					$('<p class="xs-mcs-settings-saved-msg xs-mcs-error" style="display: inline-block;line-height: 28px;padding: 0 10px;">'+res.msg+'</p>').insertBefore(submit_btn).addClass('xs-mcs-show');
				}else{
					$('<p class="xs-mcs-settings-saved-msg xs-mcs-success" style="display: inline-block;line-height: 28px;padding: 0 10px;">Settings Saved</p>').insertBefore(submit_btn).addClass('xs-mcs-show');
					setTimeout(function(){ $('.xs-mcs-settings-saved-msg').removeClass('xs-mcs-show'); }, 3000)
				}
				setTimeout(function(){ $('.xs-mcs-settings-saved-msg').removeClass('xs-mcs-show'); }, 3000)
			}
		).always(function(a, b, c){
			if(b == 'error'){
				$('<p class="xs-mcs-settings-saved-msg xs-mcs-error" style="display: inline-block;line-height: 28px;padding: 0 10px;">'+c+'</p>').insertBefore(submit_btn).addClass('xs-mcs-show');
				setTimeout(function(){ $('.xs-mcs-settings-saved-msg').removeClass('xs-mcs-show'); }, 3000)
			}
			$('.xs-mcs-spinner').css('display', 'none');
		});
	});
	$('#xs_mcs_name , #xs_mcs_email , #xs_mcs_message').on('change',function(e){
        if(!$(this).val()){
            $(this).addClass("error");
        }else{
            $(this).removeClass("error");
        }
    });
	$('.xs_mcs_support_form').on('submit' , function(e){ 
    	e.preventDefault();
    	$('.xs-send-email-notice').hide();
        $('.xs-mail-spinner').addClass('xs_is_active');
       	$('#xs_mcs_name').removeClass("error");
        $('#xs_mcs_email').removeClass("error");
        $('#xs_mcs_message').removeClass("error"); 
    	
    	$.ajax({ 
			url:ajaxurl,
			type:'post',
			data:{'action':'xs_mcs_send_mail','data':$(this).serialize()},
			beforeSend: function(){
				if(!$('#xs_mcs_name').val()){
                    $('#xs_mcs_name').addClass("error");
                    $('.xs-send-email-notice').removeClass('notice-success');
                    $('.xs-send-email-notice').addClass('notice');
                    $('.xs-send-email-notice').addClass('error');
                    $('.xs-send-email-notice').addClass('is-dismissible');
                    $('.xs-send-email-notice p').html('Please fill all the fields');
                    $('.xs-send-email-notice').show();
                    $('.xs-notice-dismiss').show();
                    window.scrollTo(0,0);
                    $('.xs-mail-spinner').removeClass('xs_is_active');
                    return false;
                }
                 if(!$('#xs_mcs_email').val()){
                    $('#xs_mcs_email').addClass("error");
                    $('.xs-send-email-notice').removeClass('notice-success');
                    $('.xs-send-email-notice').addClass('notice');
                    $('.xs-send-email-notice').addClass('error');
                    $('.xs-send-email-notice').addClass('is-dismissible');
                    $('.xs-send-email-notice p').html('Please fill all the fields');
                    $('.xs-send-email-notice').show();
                    $('.xs-notice-dismiss').show();
                    window.scrollTo(0,0);
                    $('.xs-mail-spinner').removeClass('xs_is_active');
                    return false;
                }
                 if(!$('#xs_mcs_message').val()){
                    $('#xs_mcs_message').addClass("error");
                    $('.xs-send-email-notice').removeClass('notice-success');
                    $('.xs-send-email-notice').addClass('notice');
                   	$('.xs-send-email-notice').addClass('error');
                    $('.xs-send-email-notice').addClass('is-dismissible');
                    $('.xs-send-email-notice p').html('Please fill all the fields');
                    $('.xs-send-email-notice').show();
                    $('.xs-notice-dismiss').show();
                    window.scrollTo(0,0);
                    $('.xs-mail-spinner').removeClass('xs_is_active');
                    return false;
                }
                $('.xs-send-mail').prop('disabled',true);
				$(".xs_mcs_support_form :input").prop("disabled", true);
    			$("#xs_mcs_message").prop("disabled", true);
			},
			success: function(res){
				$('.xs-send-email-notice').find('.xs-notice-dismiss').show();
				$('.xs-mcs-send-mail').prop('disabled',false);
				$(".xs_mcs_support_form :input").prop("disabled", false);
    			$("#xs_mcs_message").prop("disabled", false);
				if(res.status == true){
					$('.xs-send-email-notice').removeClass('error');
					$('.xs-send-email-notice').addClass('notice');
					$('.xs-send-email-notice').addClass('notice-success');
					$('.xs-send-email-notice').addClass('is-dismissible');
					$('.xs-send-email-notice p').html('Successfully sent');
					$('.xs-send-email-notice').show();
					$('.xs-notice-dismiss').show();
                   	$('.xs_support_form')[0].reset();
				}else{
					$('.xs-send-email-notice').removeClass('notice-success');
					$('.xs-send-email-notice').addClass('notice');
					$('.xs-send-email-notice').addClass('error');
					$('.xs-send-email-notice').addClass('is-dismissible');
					$('.xs-send-email-notice p').html('Sent Failed');
					$('.xs-send-email-notice').show();
					$('.xs-notice-dismiss').show()
				}
				$('.xs-mail-spinner').removeClass('xs_is_active');
			}

		});
    });
    $('.xs-notice-dismiss').on('click',function(e){
		e.preventDefault();
		$(this).parent().hide();
		$(this).hide();
	});
});