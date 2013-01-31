<?php
if (empty($_POST['price_buy_mx']) && empty($_POST['price_buy_mx_offer'])) exit;
include("conexion.php"); 
include("functions.php"); 
$cid = (int) $_POST['category'];
$c = row_field_table ("categories", "category_id", "", $cid);
if (empty($c['category_pt']))
	exit;
$pp = ( isset($_POST['featured']) && !empty($_POST['price_buy_mx_offer'])) ? $_POST['price_buy_mx_offer'] : $_POST['price_buy_mx']; // Product Price
echo load_price_from_pt ($c['category_pt'], $pp);
?>
