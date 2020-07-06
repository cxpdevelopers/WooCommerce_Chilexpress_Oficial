(function( $ ) {
	'use strict';
	
	function transform_woo_shipping_calculator() {
		if ($('.woocommerce-shipping-calculator input[type=text][name=calc_shipping_state]').length) {
		 	var $state = $('.woocommerce-shipping-calculator input[type=text][name=calc_shipping_state]');
		 	var $city = $('.woocommerce-shipping-calculator input[type=text][name=calc_shipping_city]');
		 	var $state_parent = $state.parents('p');
		 	var $city_parent = $city.parents('p');
		 	
		 	var state_value = $state.val();
		 	var city_value = $city.val();

		 	var $new_state = $('<select name="calc_shipping_state" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_statex" placeholder="'+$state.attr('placeholder')+'" data-placeholder="'+$state.attr('placeholder')+'"><option value="'+state_value+'" selected="selected">Cargando Región...</option></select>');
		 	var $new_city = $('<select name="calc_shipping_city" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_cityx" placeholder="'+$city.attr('placeholder')+'" data-placeholder="'+$city.attr('placeholder')+'"><option value="'+city_value+'" selected="selected"> Cargando Comuna...</option></select>');


		 	$state_parent.append($new_state);
		 	$state.remove();
		 	
		 	$city_parent.append($new_city);
		 	$city.remove();
		
			$new_state.select2( { minimumResultsForSearch: 5 } );
			$new_city.select2( { minimumResultsForSearch: 5 } )

		 	jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=obtener_regiones&nonce="+woocommerce_chilexpress.nonce,
					success: function(result){
						if (result.regiones) {
							var regiones_html = '';
							for(var k in result.regiones) {
								regiones_html += '<option value="' + k + '" ' + (state_value == k ? 'selected="selected"':'') + '>' + result.regiones[k] + '</option>';
							}
							$new_state.html(regiones_html);
						} else {
							$new_state.html('');
						}

						state_value = $new_state.val();

						jQuery.ajax({
							type: "post",
							url: woocommerce_params.ajax_url,
							dataType: 'json',
							data: "action=obtener_comunas_desde_region&region="+state_value+"&nonce="+woocommerce_chilexpress.nonce,
							success: function(result){
								if (result.comunas) {
									var comunas_html = '';
									for(var k in result.comunas) {
										comunas_html += '<option value="' + k + '" ' + (city_value == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
									}
									$new_city.html(comunas_html);
								} else {
									$new_city.html('');
								}
							}
						});


					}
				});

	 		$new_state.on('change', function(event) {

				state_value = $new_state.val();
				city_value = $new_city.val();
				$new_city.html('<option value="'+city_value+'" selected="selected"> Cargando Comuna...</option>')

				jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=obtener_comunas_desde_region&region="+state_value+"&nonce="+woocommerce_chilexpress.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += '<option value="' + k + '" ' + (city_value == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
							}
							$new_city.html(comunas_html);
						} else {
							$new_city.html('');
						}
					}
				});

	 		});
	 	// A veces solo a veces, solo cambia el county pero no el city asi que en ese caso debemos solo trabajar con el city
	 	} else if ($('.woocommerce-shipping-calculator input[type=text][name=calc_shipping_city]').length) {
			var $city = $('.woocommerce-shipping-calculator input[type=text][name=calc_shipping_city]');
			var $state = $('.woocommerce-shipping-calculator select[name=calc_shipping_state]');
		 	var $city_parent = $city.parents('p');
		 	var city_value = $city.val();		 	
		 	var $new_city = $('<select name="calc_shipping_city" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_cityx" placeholder="'+$city.attr('placeholder')+'" data-placeholder="'+$city.attr('placeholder')+'"><option value="'+city_value+'" selected="selected"> Cargando Comuna...</option></select>');

	
		 	$city_parent.append($new_city);
		 	$city.remove();

			state_value = $("#calc_shipping_state,#calc_shipping_statex").val();

			jQuery.ajax({
				type: "post",
				url: woocommerce_params.ajax_url,
				dataType: 'json',
				data: "action=obtener_comunas_desde_region&region="+state_value+"&nonce="+woocommerce_chilexpress.nonce,
				success: function(result){
					if (result.comunas) {
						var comunas_html = '';
						for(var k in result.comunas) {
							comunas_html += '<option value="' + k + '" ' + (city_value == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
						}
						$new_city.html(comunas_html);
					} else {
						$new_city.html('');
					}
				}
			});

			$("#calc_shipping_state,#calc_shipping_statex").on('change', function(event) {

				state_value = $state.val();
				city_value = $new_city.val();
				$new_city.html('<option value="'+city_value+'" selected="selected"> Cargando Comuna...</option>')

				jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=obtener_comunas_desde_region&region="+state_value+"&nonce="+woocommerce_chilexpress.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += '<option value="' + k + '" ' + (city_value == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
							}
							$new_city.html(comunas_html);
						} else {
							$new_city.html('');
						}
					}
				});

	 		});
	 		
	 	}
	}
	 $(function() {
	 	transform_woo_shipping_calculator();

	 	$('.shipping-calculator-button').live('click', function(ev) {
	 		if ($('.woocommerce-shipping-calculator input[type=text][name=calc_shipping_state]').length ||
	 			$('.woocommerce-shipping-calculator input[type=text][name=calc_shipping_city]').length
	 			) {
	 			transform_woo_shipping_calculator();
	 		}
	 	})
	 	
	 	/////////////////////////////////////
	 	$('form.woocommerce-checkout #billing_state').on('change', function(ev) {
	 		var region = $(ev.currentTarget).val();
	 		var city_value = $('form.woocommerce-checkout #billing_city').val();
	 		$('form.woocommerce-checkout #billing_city').html('<option value="'+city_value+'" selected="selected">Cargando comunas...</option>');
	 		jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=obtener_comunas_desde_region&region="+region+"&nonce="+woocommerce_chilexpress.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += '<option value="' + k + '" ' + (city_value == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
							}
							$('form.woocommerce-checkout #billing_city').html(comunas_html);
						} else {
							$('form.woocommerce-checkout #billing_city').html('');
						}
					}
				});
	 	});
	 	/////////////////////////////////////
	 	$('form.woocommerce-checkout #shipping_state').on('change', function(ev) {
	 		var region = $(ev.currentTarget).val();
	 		var city_value = $('form.woocommerce-checkout #shipping_city').val();
	 		$('form.woocommerce-checkout #shipping_city').html('<option value="'+city_value+'" selected="selected">Cargando comunas...</option>');
	 		jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=obtener_comunas_desde_region&region="+region+"&nonce="+woocommerce_chilexpress.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += '<option value="' + k + '" ' + (city_value == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
							}
							$('form.woocommerce-checkout #shipping_city').html(comunas_html);
						} else {
							$('form.woocommerce-checkout #shipping_city').html('');
						}
					}
				});
	 	});
	 	/////////////////////////////////////
	 	['shipping','billing'].forEach( function(source){


	 	if ( $('.woocommerce-MyAccount-content .woocommerce-address-fields #'+source+'_city').length ) {
	 		var $user_city = $('.woocommerce-MyAccount-content .woocommerce-address-fields #'+source+'_city');
	 		var $user_state = $('.woocommerce-MyAccount-content .woocommerce-address-fields #'+source+'_state');
	 		if ($user_state.get(0) && $user_state.get(0).tagName === 'SELECT' && $user_city.attr('type') === 'text') {

	 			var $user_city_parent = $user_city.parents('span');
	 			var user_city_value = $user_city.val();

	 			var $new_user_city = $('<select name="'+source+'_city" style="width: 100%" class="wc-enhanced-select" id="billing_city" placeholder="'+$user_city.attr('placeholder')+'" data-placeholder="'+$user_city.attr('placeholder')+'"><option value="'+user_city_value+'" selected="selected"> Cargando Comuna...</option></select>');

	 			$user_city_parent.append($new_user_city);
			 	$user_city.remove();
				$new_user_city.select2( { minimumResultsForSearch: 5 } );


				var user_state_value = $user_state.val();


				jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=obtener_comunas_desde_region&region="+user_state_value+"&nonce="+woocommerce_chilexpress.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += '<option value="' + k + '" ' + (user_city_value == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
							}
							$new_user_city.html(comunas_html);
						} else {
							$new_user_city.html('');
						}
					}
				});

				$user_state.on('change', function(ev) {
			 		var region = $(ev.currentTarget).val();
			 		var user_city_value = $new_user_city.val();
			 		$new_user_city.html('<option value="'+user_city_value+'" selected="selected">Cargando comunas...</option>');
			 		jQuery.ajax({
							type: "post",
							url: woocommerce_params.ajax_url,
							dataType: 'json',
							data: "action=obtener_comunas_desde_region&region="+region+"&nonce="+woocommerce_params.nonce,
							success: function(result){
								if (result.comunas) {
									var comunas_html = '';
									for(var k in result.comunas) {
										comunas_html += '<option value="' + k + '" ' + (user_city_value == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
									}
									$new_user_city.html(comunas_html);
								} else {
									$new_user_city.html('');
								}
					}
						});
			 	});
	 		}	
	 	}
	 });

	 	$('a.tracking-link').on('click', function(ev) {

	 		var old_text = $(ev.currentTarget).text();
	 		if (old_text === 'Cargando...') return;
	 		$(ev.currentTarget).text('Cargando...');
	 		jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=track_order&nonce="+woocommerce_chilexpress.nonce+"&pid="+$(this).data('pid')+"&ot="+$(this).data('ot'),
					success: function(result){
						var data = {};
						$(ev.currentTarget).text(old_text);
						if (result.error) {
							alert(result.error);
							return;
						}
						if (result.response && result.response.data) {
							data = result.response;
						} else {
							data = result;
						}
						$(this).WCBackboneModal({
								template: 'wc-modal-track-order',
								variable : data
							});

						setTimeout(function(){
							if (data.data.trackingEvents.length) {
								var html = '';
								$.each(data.data.trackingEvents,function(index, item){
									html += '<tr><td>'+item.eventDate+'</td><td>'+item.eventHour+'</td><td>'+item.description+'</td><td></td></tr>'
								});
								$("#wc-chilexpress-events > tbody").html(html);
							} else {
								$("#wc-chilexpress-events > tbody > tr > td").text('No existen eventos aún para este envio.');
							}
						},500);
						
						
					}
				});
			
	 	});

	 	function updateShippingCartCalculatorLabel() {
		 	$("#shipping_method label").each(function(index, el) {
	 			if ($(el).text().indexOf('Chilexpress') > -1) {
		 			$(el).html($(el).text().replace('Chilexpress', '<img src="'+woocommerce_chilexpress.base_url+'imgs/logo-chilexpress.png" style="width: 120px; margin-right: 0.2em; margin-top:2px; margin-bottom:-2px;" />'));
		 		}
		 	});
	 	}

	 	$(document.body).on('change', '#shipping_method input[type=radio].shipping_method', function(ev){ 
  			for(var i = 100; i < 2000; i = i + 50) {
	  			setTimeout(function(){
	  				updateShippingCartCalculatorLabel();
	  			}, i);
  			}
		});

	 	$(document.body).on('updated_wc_div', function(ev) { 
	 		updateShippingCartCalculatorLabel();
	 	});
	 
	 	$(document.body).on('updated_checkout', function(ev) {
	 		updateShippingCartCalculatorLabel();
	 	});

	 	updateShippingCartCalculatorLabel();




	 });

})( jQuery );
