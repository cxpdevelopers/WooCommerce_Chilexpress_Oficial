(function( $ ) {
	'use strict';

	 $(function() {

	 	$('.select-county,.select-city').select2();
	 	$('.select-county').on('change',function(ev) {
	 		var city_$ = null;
	 		var county = $(ev.currentTarget).val();
	 		if($(ev.currentTarget).data('city')) {
	 			city_$ = $(document.getElementById($(ev.currentTarget).data('city')));
	 		}
	 		if(city_$){
				
				city_$.html('<option value="'+county+'">Cargando...</option>');
				  
				jQuery.ajax({
					type: "post",
					url: ajax_var.url,
					dataType: 'json',
					data: "action=obtener_comunas_desde_region&region="+county+"&nonce=" + ajax_var.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += '<option value="' + k + '">' + result.comunas[k] + '</option>';
							}
							city_$.html(comunas_html);

							city_$.siblings("input").val(city_$.val())

						} else {
							city_$.html('');
						}
					}
				});
				
	 		}
	 	});

	 	$('.select-city').on('change',function(ev) {
	 		$(ev.currentTarget).siblings("input").val($(ev.currentTarget).val());
	 	});

	 	$('.tracking a').on('click', function(ev) {
	 		var old_text = $(ev.currentTarget).text();
	 		if (old_text === 'Cargando...') return;
	 		$(ev.currentTarget).text('Cargando...');
	 		jQuery.ajax({
					type: "post",
					url: ajax_var.url,
					dataType: 'json',
					data: "action=track_order&pid="+$(this).data('pid')+"&ot="+$(this).data('ot'),
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

	 	/*********/
	 	if ($('div.edit_address #_billing_city').length) {
		 	var $state = $('div.edit_address #_billing_state');
		 	var $city = $('div.edit_address #_billing_city');
		 	
		 	var $state_parent = $state.parents('p');
		 	var $city_parent = $city.parents('p');
		 	
		 	var state_value = $state.val();
		 	var city_value = $city.val();

		 	var $new_state = $('<select name="_billing_state" id="_billing_state" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_statex" placeholder="'+$state.attr('placeholder')+'" data-placeholder="'+$state.attr('placeholder')+'"><option value="'+state_value+'" selected="selected">Cargando Región...</option></select>');
		 	var $new_city = $('<select name="_billing_city" id="_billing_city" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_cityx" placeholder="'+$city.attr('placeholder')+'" data-placeholder="'+$city.attr('placeholder')+'"><option value="'+city_value+'" selected="selected"> Cargando Comuna...</option></select>');


		 	$state_parent.append($new_state);
		 	$state.remove();
		 	
		 	$city_parent.append($new_city);
		 	$city.remove();
		
			$new_state.select2( { minimumResultsForSearch: 5 } );
			$new_city.select2( { minimumResultsForSearch: 5 } )

			

		 	jQuery.ajax({
					type: "post",
					url: ajax_var.url,
					dataType: 'json',
					data: "action=obtener_regiones",
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
							url: ajax_var.url,
							dataType: 'json',
							data: "action=obtener_comunas_desde_region&region="+state_value,
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
					url: ajax_var.url,
					dataType: 'json',
					data: "action=obtener_comunas_desde_region&region="+state_value,
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
	 	if ($('div.edit_address #_shipping_city').length) {
		 	var $state2 = $('div.edit_address #_shipping_state');
		 	var $city2 = $('div.edit_address #_shipping_city');
		 	
		 	var $state_parent2 = $state2.parents('p');
		 	var $city_parent2 = $city2.parents('p');
		 	
		 	var state_value2 = $state2.val();
		 	var city_value2 = $city2.val();

		 	var $new_state2 = $('<select name="_shipping_state" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_statex" placeholder="'+$state.attr('placeholder')+'" data-placeholder="'+$state.attr('placeholder')+'"><option value="'+state_value+'" selected="selected">Cargando Región...</option></select>');
		 	var $new_city2 = $('<select name="_shipping_city" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_cityx" placeholder="'+$city.attr('placeholder')+'" data-placeholder="'+$city.attr('placeholder')+'"><option value="'+city_value+'" selected="selected"> Cargando Comuna...</option></select>');


		 	$state_parent2.append($new_state2);
		 	$state2.remove();
		 	
		 	$city_parent2.append($new_city2);
		 	$city2.remove();
		
			$new_state2.select2( { minimumResultsForSearch: 5 } );
			$new_city2.select2( { minimumResultsForSearch: 5 } )

			

		 	jQuery.ajax({
					type: "post",
					url: ajax_var.url,
					dataType: 'json',
					data: "action=obtener_regiones",
					success: function(result){

						if (result.regiones) {
							var regiones_html = '';
							for(var k in result.regiones) {
								regiones_html += '<option value="' + k + '" ' + (state_value2 == k ? 'selected="selected"':'') + '>' + result.regiones[k] + '</option>';
							}
							$new_state2.html(regiones_html);
						} else {
							$new_state2.html('');
						}

						state_value2 = $new_state2.val();

						jQuery.ajax({
							type: "post",
							url: ajax_var.url,
							dataType: 'json',
							data: "action=obtener_comunas_desde_region&region="+state_value2,
							success: function(result){
								if (result.comunas) {
									var comunas_html = '';
									for(var k in result.comunas) {
										comunas_html += '<option value="' + k + '" ' + (city_value2 == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
									}
									$new_city2.html(comunas_html);
								} else {
									$new_city2.html('');
								}
							}
						});


					}
				});

	 		$new_state2.on('change', function(event) {

				state_value2 = $new_state2.val();
				city_value2 = $new_city2.val();
				$new_city2.html('<option value="'+city_value2+'" selected="selected"> Cargando Comuna...</option>')

				jQuery.ajax({
					type: "post",
					url: ajax_var.url,
					dataType: 'json',
					data: "action=obtener_comunas_desde_region&region="+state_value2,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += '<option value="' + k + '" ' + (city_value2 == k ? 'selected="selected"':'') + '>' + result.comunas[k] + '</option>';
							}
							$new_city2.html(comunas_html);
						} else {
							$new_city2.html('');
						}
					}
				});

	 		});
	 	}
	 	/*********/
	 	

	 });
	 

})( jQuery );
