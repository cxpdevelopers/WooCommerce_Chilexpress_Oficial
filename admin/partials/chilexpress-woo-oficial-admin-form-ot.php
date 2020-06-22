	<form action="" method="post">
		<h2>Generar OT</h2>
		<h3>Dirección de Destino</h3>
		<input type="hidden" name="subaction" value="generar"/>
		<input type="hidden" name="referer" value="<?php echo esc_attr($_SERVER["HTTP_REFERER"]); ?>"/>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">Código de comuna de destino</th>
					<td><input type="text" name="generar_ot[codigo_comuna_destino]" disabled="disabled" value="<?php echo $order_data["shipping"]["city"]; ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">Nombre de calle</th>
					<td><input type="text" name="generar_ot[calle_destino]" disabled="disabled" value="<?php echo $order_data["shipping"]["address_1"]; ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">Número de calle</th>
					<td><input type="text" name="generar_ot[numero_calle_destino]" disabled="disabled" value="<?php echo $order_data["shipping"]["address_2"]; ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">Complemento</th>
					<td><input type="text" name="generar_ot[complemento_destino]" disabled="disabled" value="<?php echo esc_attr($complemento); ?>" class="regular-text"></td>
				</tr>
			</tbody>
		</table>
		<h3>Dirección de devolución</h3>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">Código de comuna de devolución</th>
					<td><input type="text" name="generar_ot[codigo_comuna_devolucion]" disabled="disabled" value="<?php if(isset($options['comuna_devolucion'])){ echo $options['comuna_devolucion']; } ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">Nombre de calle</th>
					<td><input type="text" name="generar_ot[calle_devolucion]" disabled="disabled" value="<?php if(isset($options['calle_devolucion'])){ echo $options['calle_devolucion']; } ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">Número de calle</th>
					<td><input type="text" name="generar_ot[numero_calle_devolucion]" disabled="disabled" value="<?php if(isset($options['numero_calle_devolucion'])){ echo $options['numero_calle_devolucion']; } ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">Complemento</th>
					<td><input type="text" name="generar_ot[complemento_devolucion]" disabled="disabled" value="<?php if(isset($options['complemento_devolucion'])){ echo $options['complemento_devolucion']; } ?>" class="regular-text"></td>
				</tr>
			</tbody>
		</table>
		<h3>Datos del remitente</h3>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">Nombre</th>
					<td><input type="text" name="generar_ot[nombre_remitente]" disabled="disabled" value="<?php if(isset($options['nombre_remitente'])){ echo $options['nombre_remitente']; } ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">Teléfono</th>
					<td><input type="text" name="generar_ot[telefono_remitente]" disabled="disabled" value="<?php if(isset($options['telefono_remitente'])){ echo $options['telefono_remitente']; } ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">E-mail</th>
					<td><input type="text" name="generar_ot[email_remitente]" disabled="disabled" value="<?php if(isset($options['email_remitente'])){ echo $options['email_remitente']; } ?>" class="regular-text"></td>
				</tr>
			</tbody>
		</table>
		<h3>Datos del destinatario</h3>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">Nombre</th>
					<td><input disabled type="text" name="generar_ot[nombre_destinatario]" value="<?php echo $order_data["shipping"]["first_name"]." ".$order_data["shipping"]["last_name"]; ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">Teléfono</th>
					<td><input disabled type="text" name="generar_ot[telefono_destinatario]" value="<?php echo $order_data["billing"]["phone"]; ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row">E-mail</th>
					<td><input disabled type="text" name="generar_ot[email_destinatario]" value="<?php echo $order_data["billing"]["email"]; ?>" class="regular-text"></td>
				</tr>
			</tbody>
		</table>
		<h3>Armado de bultos</h3>

		<table class="widefat striped">
			<thead>
				<tr>
					<th>Id</th>
					<th>Nombre</th>
					<th>Cantidad</th>
					<th>Dimensiones</th>
					<th>Peso Total</th>
					<th>Bulto</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$paquetes = count($order->get_items());

					foreach ($order->get_items() as $item_key => $item ):

					    ## Using WC_Order_Item methods ##

					    // Item ID is directly accessible from the $item_key in the foreach loop or
					    $item_id = $item->get_id();

					    ## Using WC_Order_Item_Product methods ##

					    $product      = $item->get_product(); // Get the WC_Product object

					    $product_id   = $item->get_product_id(); // the Product id

					    $item_type    = $item->get_type(); // Type of the order item ("line_item")

					    $item_name    = $item->get_name(); // Name of the product
					    $quantity     = $item->get_quantity();  
					   

					    // Get data from The WC_product object using methods (examples)
					    $product        = $item->get_product(); // Get the WC_Product object
					    $stock_quantity = $product->get_stock_quantity();
				    ?>
				    <tr>
					<td>
						<span><?php echo $product_id; ?></span>
					</td>
					<td>
						<span><?php echo $item_name; ?></span>
					</td>
					<td>
						<span><?php echo $quantity; ?></span>
					</td>
					<td>
						<?php  echo wc_format_dimensions($product->get_dimensions(false));  ?> 
					</td>
					<td>
						<?php  echo $product->get_weight() ? wc_format_weight($product->get_weight()*$quantity) : '0kg'; ?>
					</td>
					<td>
						<select name="paquetes[<?php echo $product_id; ?>]">
							<?php for($i=1;$i<=$paquetes;$i++){ ?>
							<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				    <?php
				endforeach;

				?>
				
			</tbody>
		</table>
		<?php if($continuar_orden) {  submit_button("Guardar"); } ?>
	</form>
