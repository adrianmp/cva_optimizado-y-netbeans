<?php
include 'include/conexion.php';
$product_status = (int) $_GET['product_status'];
$module = $_GET['module'];
$title_action = ($product_status) ? $t['delete'] : $t['restore'];

$sql_users = "SELECT user_id, user_name FROM users";
$result_users = mysql_query($sql_users);
if (!$result_users) { die('Invalid query: ' . mysql_error());}
while($row_users = mysql_fetch_array($result_users, MYSQL_BOTH)) {
	$shortusername = explode(" ", $row_users['user_name']);
	$users[$row_users['user_id']] = stripslashes($shortusername[0]);
}

/*
$sql_categories = "SELECT category_id, category_slug FROM categories";
$result_categories = mysql_query($sql_categories);
while($row_categories = mysql_fetch_array($result_categories, MYSQL_BOTH))
	$categories[$row_categories['category_id']] = $row_categories['category_slug'];
*/
	
$sql = "SELECT * FROM products WHERE product_status = " . $product_status;
if ($module == "stock") {
	$sql .= " AND product_stock";
}
if ($module == "edit-product" || (isset($_GET['category_id']) && $_GET['category_id'])) {
	$category_id = (int) $_GET['category_id'];
	if ($module == "edit-product")
		$sql .= " AND product_id != " . (int) $_GET['product_id'];
	$sql .= " AND product_category = " . $category_id . " ORDER BY product_uploaded DESC";
}
if ($module == "dashboard" || ((!isset($_GET['category_id']) || !$_GET['category_id']) && $module == "products"))
	$sql .= " AND product_featured = 1 AND product_featured_end <= CURRENT_DATE() + 1 ORDER BY product_featured_end ASC LIMIT 17";

if ($module == "stats") {
	$sql .= " ORDER BY product_visits DESC";
}

if ($module == "notifications") {
	$sql .= " ORDER BY product_last_visit DESC LIMIT 1";
}

if ($module == "trash" && (!isset($_GET['category_id']) || !$_GET['category_id'])) {
	$sql .= " ORDER BY product_updated DESC LIMIT 10";
}

//exit ($sql);
$result = mysql_query ($sql);
$nproducts = mysql_num_rows($result);

//exit ($nproducts);

if (!$nproducts)
	if ($module == "products" || $module == "trash")
		exit('<div id="warning">'.$t['none'].'</div><p><a href="add-product.php?category='.$category_id.'" class="button" title="'.$t['add-product'].'"> + '.$t['add-product'].'</a></p>');
	else
		exit;
		
if ($module == "edit-product") {
	echo '<label for="select-related">Seleccione: </label><select style="width:467px" name="select-related" id="select-related">';
	while($row = mysql_fetch_array($result, MYSQL_BOTH)) {		
		echo '<option value="'.$row['product_id'].'">'.stripslashes($row['product_name']).'</option>';
	}
	echo '</select> <a href="#" class="add-related">Agregar producto</a>';
	exit;
}
if ($module == "notifications") {
	$p = mysql_fetch_row($result);
	echo '<img src="'.uri_host().$p[13].'" style="float: left; width: 100px; height: 100px" width="64" height="64" />';
	echo '<b>'.stripslashes($p[2]).'</b><br />';
	if (!empty($p[1]))
		echo 'IM: ' . explodeSkus ($p[1], "https://www.imstores.com/ingrammicromx/ProductDetail.aspx?sku=STR_REPLACE");	
	if (!empty($p[22]))
		echo 'CVA: ' . explodeSkus ($p[22], "cva.php?clave=STR_REPLACE");
	if (!empty($p[23]))
		echo 'CTIN: ' . explodeSkus ($p[23], "http://ctonline.mx/producto?c=STR_REPLACE");
    echo '<br />Precio sin IVA: <strong>'. number_format($p[6]) .'</strong> Precio P&uacute;blico: <strong>'. number_format($p[8]) .'</strong><br />';
    echo '&#8250; <a href="'.uri_host().'/'.$p[3].'.html" target="_blank">Ver</a> ';
    echo '&#8250; <a href="'.uri_host().'/edit-product.php?id='.$p[0].'" target="_blank">Editar</a>';	
	exit;
}
?>
<div id="live_filter">
<?php if ($module == "products" || $module == "trash"){?>
<a id="controlbtn" class="open gbutton" href="#" alt="Show/View your stuffs">&lt; Ocultar Categor&iacute;as</a>
<a href="add-product.php?category=<?php echo $category_id?>" class="gbutton" title="<?php echo $t['add-product']?>"><img src="/images/plus.gif" alt="Agregar Producto" width="10" height="10" /> <?php echo $t['add-product']?></a> 
<input  type="text" class="filter" onclick="this.value=''" value="Filtrar Productos" name="liveFilter" size="50" /> 
<a href="products-list.php?category_id=<?php echo $category_id;?>&product_status=<?php echo $product_status;?>&module=<?php echo $module;?>" class="gbutton update">Actualizar</a>
<?php } ?>
<table id="minimalist" border="0" cellpadding="0" cellspacing="1">
    <thead>
    	<tr>
			<th scope="col">Actualizar</th>
			<th scope="col">Actualizado</th>
			<th scope="col">Promo</th>
			<th scope="col">Por</th>
			<th scope="col">S</th>
			<th scope="col">ID</th>
			<th scope="col"><?php echo $t['name']." & ".$t['desc']?> (<?php echo $nproducts;?> <?php echo $module == "dashboard" ? "&uacute;ltimos" : "encontrados";?>)</th>
			<th scope="col">IM</th>
			<th scope="col">CVA</th>
			<th scope="col">CT</th>
			<th scope="col">R</th>
			<th scope="col">A</th>
			<th scope="col">Actualizado</th>
			<th scope="col">Precio($)</th>
			<th scope="col">Vis</th>
            <th scope="col" colspan="2">Opc.</th>
        </tr>
    </thead>
    <tbody>
	<?php
	while($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$edit = '<a href="edit-product.php?id='.$row['product_id'].'" title="'.$t['edit'].' '.stripslashes($row['product_name']).'"><img border="0" src="images/pencil.png" width="16" height="16" /></a>';
		echo '<tr id="product'.$row['product_id'].'">';
		echo '<td align="center">' . date_mysql_normal($row['product_next_update'], "es", FALSE) . '</td>';
		echo '<td align="center">' . how_long_ago(convert_datetime_timestamp($row['product_updated'])) . '</td>';
		echo '<td align="center">' . ( $row['product_featured'] ? date_mysql_normal($row['product_featured_end'], "es", FALSE) : "" ) . '</td>';
		echo '<td align="center">' . $users[$row['product_user']] . '</td>';
		echo '<td align="center">' . ( $row['product_stock'] ? $row['product_stock'] : "" ) . '</td>';
		echo '<td align="center">' . $row['product_id'] . '</td>';
		echo '<td>';
		echo '<a href="edit-product.php?id='.$row['product_id'].'" title="'.$t['edit'].' '.stripslashes($row['product_name']).'">'.stripslashes($row['product_name']).'</a>';
		echo '</td>';
		echo "<td>" . explodeSkus ($row['product_sku'], "https://www.imstores.com/ingrammicromx/ProductDetail.aspx?sku=STR_REPLACE") . "</td>";
		echo "<td>" . explodeSkus ($row['product_clave_cva'], "cva.php?clave=STR_REPLACE") . "</td>";
		echo "<td>" . explodeSkus ($row['product_clave_ctin'], "http://ctonline.mx/producto?c=STR_REPLACE") . "</td>";
		echo '<td align="center">'.(empty($row['product_related']) ? "" : count(explode(",",$row['product_related']))).'</td>';
		echo '<td align="center">'.(empty($row['product_attributes']) ? "" : count(explode(",",trim($row['product_attributes'], ",")))).'</td>';
		echo '<td align="center">'.date_mysql_normal($row['product_updated']).'</td>';
		echo '<td class="public_price" align="center"><abbr class="buy_price" title="'.number_format($row['product_buy_mx'], 2).'">'.number_format($row['product_price_mx'], 2).'</abbr></td>';
		//echo '<td><a href="http://ctonline.mx/producto?c='.$row['product_clave_ctin'].'" target="_blank">'.$row['product_clave_ctin'].'</a></td>';
		echo '<td align="center">'. $row['product_visits'] .'</td>';
		echo '<td align="center"><a href="product-details.php?id='.$row['product_id'].'&full=1" id="dialog_link" title="'.stripslashes($row['product_name']).'" class="dialog_link"><img border="0" src="images/information.png" width="16" height="16" /></a></td>';
		$product_basename = path_info($row['product_image_uri'], "basename");
		echo '<td><a target="_blank" href="/'.$row['product_slug'].'.html" title="Ir a '.stripslashes($row['product_name']).'"><img border="0" src="images/bullet_go.png" width="16" height="16" /></a></td>';
		echo "</tr>\n";
	}
	?>
	
    </tbody>
</table>
</div>
<script type="text/javascript">$("tr:odd").css("background-color", "#F9F9F9");</script>
