<?php include("cabecera.php");?>
<link type="text/css" rel="stylesheet" href="jquery-ui-1.7.1.custom.css"/>
<link type="text/css" rel="stylesheet" href="default.css"/>
<script type="text/javascript" src="jquery-ui-1.7.1.min.js"></script>		
<script type="text/javascript" src="jquery-ui.datepicker.min.js"></script>
<script type="text/javascript" src="add-product.js"></script>
<script src="AjaxUpload.2.0.min.js"></script>
<?php
	require("functions.php");
	require_once("class.inputfilter.php");
	include "es.php";
	$ifilter = new InputFilter();
	$_GET = $ifilter->process($_GET);
	$brand = $_GET['product_id'];
	$sql = "SELECT * FROM new_products WHERE product_id =".$brand;
	$result = mysql_query($sql);		
	$row = mysql_fetch_row($result);
	if($row)
	{
		$large_image_name = str_utime();
?>
	<p><span id="countdown" style="display:none"></span></p>
	<div id="errors"></div>
	<form id="form" style="width: 770px;" class="cmxform" name="new-product" enctype="multipart/form-data">
		<input type="hidden" name="product_id" value="<?php echo $row[0];?>" id="product_id" />
		<input name="submit" type="submit" class="button" id="submit" value="<?php echo $t['add-product']?>" />
		<span class="productstatus" style="margin-left:50px; border-bottom:6px solid #679902">
			<strong>Producto Existente</strong>: <input type="checkbox" name="product_status" value="1" checked="checked" />
		</span>		
		<a class="cancel" id="cancel" style="margin-left:270px" href="javascript:jQuery.fancybox.close();" ><?php echo $t['cancel']?></a>
		<input id="real_file_name" type="hidden" name="real_file_name" value="<?php echo $large_image_name;?>" />
		<div class="clear"></div>
		<label for="sku">Claves:</label>
		IM: <input id="sku" class="isrepeated" name="sku" type="text" size="12" value="" /> 
		CVA: <input id="cva" class="isrepeated" name="cva" type="text" size="12" value="<?php echo $row[2];?>" /> 
		CT: <input id="ctin" class="isrepeated" name="ctin" type="text" size="12" value="" /><br />		
		<label for="categories"><?php echo $t['category']?>: </label>
		<?php echo product_related_table("add-product", "category", "categories", $row[7]); ?> 
		<strong><?php echo $t['brand']?></strong>:
		<?php echo product_related_table("add-product", "brand", "brands", $row[1]); ?>
		<br />
		<label for="price_buy_mx"><?php echo $t['price']?> Normal</label>
		<input type="hidden" name="moneda" value="<?php echo $row[11];?>" id="moneda" />
		$ <input id="price_buy_mx" name="price_buy_mx" type="text" size="18" value="<?php echo $row[10];?>" class="number" />		
		<?php if($row[14] == "Sin Descuento"):?>
			<strong>Producto en Oferta?</strong>: <input id="featured" name="featured" class="offer" type="checkbox" value="1" />
		<?php else:?>
			<strong>Producto en Oferta?</strong>: <input id="featured" name="featured" class="offer" type="checkbox" checked="yes" value="1" />
		<?php endif;?>
		<div class="extra">
			<label for="price_buy_mx_offer"><?php echo $t['price']?> Oferta: </label> 
			$ <input id="price_buy_mx_offer" name="price_buy_mx_offer" type="text" size="18" value="<?php echo $row[15];?>" class="required number offer" />
			<strong>Hasta</strong>: 
			<input class="datepicker offer" type="text" size="10" value="<?php echo $row[17];?>" id="featured_end" name="featured_end" />			
		</div>
		<div class="clear"></div>
		<label for="price_mx"><?php echo $t['price']?> P&uacute;blico</label>
		$ <input id="price_mx" name="price_mx" type="text" size="18" value="" class="required number" /> <span style="background-color:#68BADA;color:white" id="updated"></span> 
		<strong>Actualizar el</strong>: <input class="datepicker" type="text" size="10" value="" id="next_update" name="next_update" />
		<input name="pricechanged" type="hidden" id="pricechanged" value="TRUE" size="25" />
		<div style="clear:both"></div>
		<div id="load-attributes" style="display:none"></div>
		<div style="clear:both"></div>		
		<label for="pn">N&uacute;mero de Parte:</label>
		<input id="pn" class="isrepeated" name="pn" type="text" size="30" value="<?php echo $row[3];?>" /> 
		<strong>Stock</strong>: <input id="stock" name="stock" type="text" size="17" value="0" />
		<div id="related">
			<label for="categories"><?php echo $t['category']?>: </label>
			<?php echo product_related_table("edit-product", "related_category", "categories", 0); ?>
			<div id="related_category_products"></div>
			<div class="stack">
				<?php
				if (!empty($epid['product_related'])) {
					$arp = array_unique(explode(",", $epid['product_related']));
					foreach ($arp as $rp) {
						$urp = row_field_table("products", "product_id", "", $rp);
						if ($rp == $_GET['id']) continue;
						echo '<div id="rp-' . $rp .'" class="rp-row">&bull; <input size="70" value="'. $rp .'" type="hidden" name="related_products[]" /> ' . stripslashes($urp['product_name']);
						if (!$urp['product_status']) echo ' <span class="featured-date red">Agotado</span>';
						echo ' <a onclick="removeRelatedProduct('. $rp .')" >Remover</a></div>';
					}
				}
				?>
			</div>
		</div>
		<label for="name"><?php echo $t['name']?>: </label>
		<input id="name" name="name" type="text" size="92" value="<?php echo $row[4]; ?>" class="required" /> <br />
		<label for="info_uri">Informaci&oacute;n de:</label>
		<input id="info_uri" name="info_uri" type="text" size="92" value="" />
		<br />
		<label for="slug"><?php echo $t['slug']?>: </label>
		<input name="slug" type="text" id="slug" onclick="productSlug();" value="" size="92" /> <br />
		<label for="file"><?php echo $t['select'], " ", $t['image']?>: </label>
		<input type="hidden" name="product_image_uri" id="product_image_uri" value="<?php echo $row[20]; ?>" />
		<img src="<?php echo $row[20]; ?>" width="100" height="100" />
		<!--label for="desc"><?php echo $t['desc']?>: </label-->
		<textarea id="desc" name="desc" rows="15" cols="90"><?php echo $row[5]."\n".$row[6]; ?></textarea>
		<div style="clear:both"></div>
		<input name="submit" type="submit" class="button" id="submit" value="<?php echo $t['add-product']?>" />
		<a class="cancel" style="margin-left:450px" href="javascript:history.back(1)"><?php echo $t['cancel']?></a>
	</form>
	
<?php
	}	
	else
		echo "<h3>El articulo que intenta agrega ya ha sido agregado</h3><button id='bc'>Cerrar</button>";
?>
	<div id="errores"></div>
<?php include("final.php");?>
