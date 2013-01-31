<?php
	require("conexion.php");
	require_once("class.inputfilter.php");
	$ifilter = new InputFilter();
	$_POST = $ifilter->process($_POST);
	$product_id =  $_POST['product_id'];
	$sql = "DELETE FROM new_products WHERE product_id =".$product_id;
	$result = mysql_query($sql);		
	echo $result;
?>
