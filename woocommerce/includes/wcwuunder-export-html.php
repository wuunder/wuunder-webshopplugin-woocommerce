<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<?php
		//require_once(ABSPATH . 'wp-admin/admin.php');
		wp_register_style( 'wcwuunder-admin-styles', dirname(plugin_dir_url(__FILE__)) .  '/assets/css/wcwuunder-admin-styles.css', array(), '', 'all' );
		wp_enqueue_style( 'wcwuunder-admin-styles' );		
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'media' );
		wp_enqueue_script( 'jquery' );
		//do_action('admin_print_styles');
		//do_action('admin_print_scripts');
	?>
	<style>
	.button-primary {
	    /*background: #0085ba;
	    border-color: #0073aa #006799 #006799;
	    -webkit-box-shadow: 0 1px 0 #006799;
	    box-shadow: 0 1px 0 #006799;
	    color: #fff;
	    text-decoration: none;
	    text-shadow: 0 -1px 1px #006799,1px 0 1px #006799,0 1px 1px #006799,-1px 0 1px #006799;*/
	    background: #8dcc00 !important;
	    border-color: #8dcc00 !important;
	    -webkit-box-shadow: 0 1px 0 #8dcc00 !important;
	    box-shadow: 0 1px 0 #8dcc00 !important;
	    color: #fff;
	    text-decoration: none;
	    text-shadow: 0 -1px 1px #8dcc00,1px 0 1px #8dcc00,0 1px 1px #8dcc00,-1px 0 1px #8dcc00 !important;
	    
	}
	.submit-wuunder{margin-top: 30px;}
	p{
		font-size: 11px;
	}
	</style>
</head>
<body>
	<form method="post" class="page-form">
		<?php $c = true; foreach ($data as $row) : ?>
			<div style="overflow:hidden">
				<div style="display:inline-block; float:left; width:33%;">
					<h2>
						<img style="width:20px;" src="<?php echo dirname(plugin_dir_url(__FILE__)) ?>/assets/images/create-icon.png">
						<span> Bestelling <?php echo $row['ordernr']; ?></span>
					</h2>
				</div>
				<div style="display:inline-block; float:left; width:67%; text-align:right;">
					<p>
					<?php if ( $row['landcode'] == 'NL' && ( empty($row['straat']) || empty($row['huisnummer']) ) ) { ?>
						<span style="color:red">Deze order bevat geen geldige straatnaam- en huisnummergegevens, en kan daarom niet worden ge-exporteerd! Waarschijnlijk is deze order geplaatst voordat de Wuunder plugin werd geactiveerd. De gegevens kunnen wel handmatig worden ingevoerd in het order scherm.</span>
					<?php } else { ?>
						<?php echo $row['formatted_address'].'<br/>'.$row['telefoon'].'<br/>'.$row['email']; ?>
					<?php } ?>
					</p>
				</div>
			</div>
			<table class="widefat">
				<thead>
					<tr>
						<th>#</th>
						<th>Productnaam</th>
						<th align="left">Gewicht (gr)</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$total_weight = 0;
				foreach ($row['bestelling'] as $product) {
					$total_weight += $product['total_weight'];?>
					<tr>
						<td><?php echo $product['quantity'].'x'; ?></td>
						<td><?php echo $product['name'].$product['variation']; ?></td>
						<td align="left"><?php echo $product['total_weight']; ?>gr</td>
					</tr>
				<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td>Totaal:</td>
						<td align="left"><?php echo $total_weight;?>gr</td>
					</tr>
				</tfoot>
			</table>
			<?php if ( $row['landcode'] == 'NL' && ( !empty($row['straat']) || !empty($row['huisnummer']) ) ) { ?>
				<input type="hidden" name="data[chamber_of_commerce_number]" value="<?php echo $row['orderid']; ?>">
				<input type="hidden" name="data[given_name]" value="<?php echo $row['firstname'] ?>">
				<input type="hidden" name="data[family_name]" value="<?php echo $row['lastname'] ?>">
				<input type="hidden" name="data[business]" value="<?php echo $row['bedrijfsnaam'] ?>">
				<input type="hidden" name="data[street_address]" value="<?php echo $row['straat'] ?>">
				<input type="hidden" name="data[house_number]" value="<?php echo $row['huisnummer'] ?>">
				<input type="hidden" name="data[house_number_suffix]" value="<?php echo $row['huisnummertoevoeging'] ?>">
				<input type="hidden" name="data[address]" value="<?php echo $row['adres1'] ?>">
				<input type="hidden" name="data[adres2]" value="<?php echo $row['adres2'] ?>">
				<input type="hidden" name="data[phone_number]" value="<?php echo $row['telefoon'] ?>">
				<input type="hidden" name="data[zip_code]" value="<?php echo $row['postcode'] ?>">
				<input type="hidden" name="data[locality]" value="<?php echo $row['woonplaats'] ?>">
				<input type="hidden" name="data[country]" value="<?php echo $row['landcode'] ?>">
				<input type="hidden" name="data[picture]" value="<?php echo $row['picture'] ?>">
				<input type="hidden" name="data[value]" value="<?php echo $row['waarde']; ?>">
				<input type="hidden" name="data[email_address]" value="<?php echo $row['email'] ?>">	
				<h2>Verzendlabel</h2>
				<div>
					<label style="width:200px; display:inline-block;">Soort verpakking*</label>
					<select name="data[kind]">
						<option value="package">Pakket</option>
						<option value="document">Document</option>
						<option value="pallet"<?php if($total_weight > 23000){echo ' selected'; }?>>Pallet</option>
					</select>
				</div>
				<? $size = explode(' x ', $product['dimensions']); ?>
				<div>
					<label style="width:200px; display:inline-block;">Pakket afmeting (LxBxH in CM)*</label>
					<input style="width:75px;" type="text" name="data[length]" placeholder="100" value="<?php echo $size[0] ?>" required> x 
					<input style="width:75px;" type="text" name="data[width]" placeholder="100" value="<?php echo $size[1]; ?>" required> x 
					<input style="width:75px;" type="text" name="data[height]" placeholder="100" value="<?php echo str_replace(' cm', '', $size[2]); ?>" required> cm
				</div>
				<div>
					<label style="width:200px; display:inline-block;">Gewicht (gram)*</label>
					<input type="text" name="data[weight]" value="<?php echo $total_weight; ?>" required>
				</div>
				<?php /*
				<div>
					<label style="width:200px; display:inline-block;">Aantal dozen</label>
					<select name="data[colli_amount]" class="colli-amount" data-orderid="<?php echo $row['orderid']; ?>">
						<option value="1" selected>1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
					</select>
				</div>
				*/ ?>
				<div>
					<?php /*
					<?php 
						if (!isset($this->settings['kenmerk'])) $this->settings['kenmerk'] = '';
						$kenmerk = str_replace('[ORDER_NR]', $row['ordernr'], $this->settings['kenmerk']);
					?>
					<label style="width:200px; display:inline-block;">Kenmerk (inhoud pakket)*</label>
					<input type="text" name="data[description]" value="<?php echo $kenmerk; ?>" required>
					*/ ?>
					<label style="width:200px; display:inline-block;">Kenmerk (inhoud pakket)*</label>
					<input type="text" name="data[description]" value="<?php foreach ($row['bestelling'] as $product) { echo $product['name'].$product['variation']; if (next($row['bestelling'])) { echo ', '; }} ?>" required>
				</div>
				<div>
					<label style="width:200px; display:inline-block; vertical-align: top; padding-top: 6px;">Persoonlijk bericht</label>
					<textarea style="width: 300px; margin-left: 1px; margin-top: 1px;" rows="4" type="text" name="data[personal_message]" value=""></textarea>
				</div>
				<input type="hidden" name="action" value="wcwuunder-export">
				<div class="submit-wuunder">
					<input type="submit" value="Vraag verzendlabel aan" class="button-wuunder button save_order button-primary tips">
				</div>
			<? } ?>
		<? endforeach; ?>
	</form>
</body>
</html>