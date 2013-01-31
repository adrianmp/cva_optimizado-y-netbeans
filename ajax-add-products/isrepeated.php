<?php
require("include/conexion.php"); 
require("include/functions.php"); 
$skus = array ("sku" => "product_sku", "cva" => "product_clave_cva", "ctin" => "product_clave_ctin", "pn" => "product_pn");
//exit($skus[$_POST['w']] . " - " . $_POST['v']);
echo count_entries ("products", $skus[$_POST['w']], trim($_POST['v'])) ? "TRUE" : "FALSE";
?>
