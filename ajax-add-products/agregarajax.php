<?php
	require("include/conexion.php");
	require("include/functions.php");
	require_once("include/class.inputfilter.php");	
	$ifilter = new InputFilter();
	$_POST = $ifilter->process($_POST);	
	$errors="";
	$name = addslashes($_POST['name']);	
	$pn = trim($_POST['pn']);
	$stock = (int) $_POST['stock'];
	$sku = trim($_POST['sku']);
	$cva = trim($_POST['cva']);
	if($cva=="")
		$errors.= "El Campo Cva esta vacio!. <br/>";
	$ctin = trim($_POST['ctin']);			
	$cat = (int) $_POST['category'];
	$brand = (int) $_POST['brand'];
	
	if(($cat==0) || ($brand==0))
		$errors.="Selecciona una Categoria/Marca!!!<br/>";
	
	$featured = isset($_POST['featured']) ? 1 : 0;
	$featured_end = ($featured) ? datepicker_to_mysql ($_POST['featured_end'], "18:00:00") : today_mysql_datetime();
	$desc = addslashes($_POST['desc']);
	$price_mx = (!isset($_POST['price_mx'])) ? 0 : str_replace(",", "", $_POST['price_mx']);
	$product_image_uri = $_POST['product_image_uri'];	
	if($_POST['info_uri']=="")
		$errors.="El campo de informacion de producto esta Vacio!!!<br/>";
	if($errors=="")
	{
		$sql = "INSERT INTO products (product_sku, product_name, product_slug, product_desc, product_brand, product_buy_mx, product_buy_mx_offer, product_price_mx, product_category, product_image_uri, product_uploaded, product_status, product_featured, product_featured_end, product_clave_cva, product_clave_ctin, product_pn, product_stock, product_info_uri, product_related, product_next_update, product_attributes, product_user_upl)
							VALUES ('" . $sku ."', '" . $name ."', '" . $_POST['slug'] ."', '" . $desc ."', '" . $brand ."', '" . str_replace(",", "", $_POST['price_buy_mx']) ."', '".str_replace(",", "", trim($_POST['price_buy_mx_offer']))."', $price_mx , $cat, '" . $product_image_uri. "', '" . today_mysql_datetime() . "', '". (isset($_POST['product_status']) ? 1 : 0) ."', $featured, '".$featured_end."', '" . $cva ."', '" . $ctin ."', '" . $pn ."', '" . $stock ."', '" . urlencode($_POST['info_uri']) ."', '". ((isset($_POST['related_products'])) ? implode(",", $_POST['related_products']) : "") ."', '". datepicker_to_mysql ($_POST['next_update'], "08:00:00") ."', '" . ((isset($_POST['attributes'])) ? implode(",", $_POST['attributes']) : "") . "', 6)";
		$query = mysql_query($sql);
		$id = mysql_insert_id();
	//echo $query;
		if($query)
		{
			$product_id =  $_POST['product_id'];
			$sql = "DELETE FROM new_products WHERE product_id =".$product_id;
			echo mysql_query($sql);
		}
		else
			echo 0;
	}
	else
		echo "<strong>".$errors."</strong>";
?>
