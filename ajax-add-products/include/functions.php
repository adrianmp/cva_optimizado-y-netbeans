<?php
/*
echo "<pre>";
	print_r ($);
echo "</pre>";
*/

if ( !function_exists('FUNC_brouserUsr') ) :
function FUNC_brouserUsr(){
	//echo FUNC_brouserUsr();
	if((ereg("Nav", $_SERVER["HTTP_USER_AGENT"]))
	|| (ereg("Gold", $_SERVER["HTTP_USER_AGENT"]))
	|| (ereg("X11", $_SERVER["HTTP_USER_AGENT"]))
	|| (ereg("Mozilla", $_SERVER["HTTP_USER_AGENT"]))
	|| (ereg("Netscape", $_SERVER["HTTP_USER_AGENT"]))
	AND (!ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])
	AND (!ereg("Konqueror",$_SERVER["HTTP_USER_AGENT"])))) $browser = "Netscape";
	elseif(ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) $browser = "MSIE";
	elseif(ereg("Lynx", $_SERVER["HTTP_USER_AGENT"])) $browser = "Lynx";
	elseif(ereg("Opera", $_SERVER["HTTP_USER_AGENT"])) $browser = "Opera";
	elseif(ereg("Netscape", $_SERVER["HTTP_USER_AGENT"])) $browser = "Netscape";
	elseif(ereg("Konqueror", $_SERVER["HTTP_USER_AGENT"])) $browser = "Konqueror";
	elseif((eregi("bot", $_SERVER["HTTP_USER_AGENT"]))
	|| (ereg("Google", $_SERVER["HTTP_USER_AGENT"]))
	|| (ereg("Slurp", $_SERVER["HTTP_USER_AGENT"]))
	|| (ereg("Scooter", $_SERVER["HTTP_USER_AGENT"]))
	|| (eregi("Spider", $_SERVER["HTTP_USER_AGENT"]))
	|| (eregi("Infoseek", $_SERVER["HTTP_USER_AGENT"]))) $browser = "Bot";
	else $browser = "Other";
	return $browser;
}
endif;

// Avoid XSS
if ( !function_exists('clean_tags') ) :
function clean_tags($s){
	$s = strip_tags($s);
	$s = stripslashes($s);
	$s = htmlentities($s);  
	$s = str_replace ("_", "", $s);
	return $s;
}
endif;

/*
$q = strip_tags($q);
$q = stripslashes($q);
$q = htmlentities($q);  // Avoid XSS
$q = str_replace ("_", "", $q); */

// Return all fields for a user
if ( !function_exists('user') ) :
function user($uid) {
	$sql = "SELECT * FROM users WHERE user_id = '$uid'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
}
endif;
// Return all fields for a client
if ( !function_exists('client') ) :
function client($cid) {
	$sql = "SELECT * FROM clients WHERE client_id = '$cid'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
}
endif;
// Return Url for current host
if ( !function_exists('uri_host') ) :
function uri_host() {
	$uri = (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) ? 'https://' : 'http://';
	$uri .= $_SERVER['HTTP_HOST'];
	return $uri;
}
endif;	

// Prevent direct access to PHP file
if ( !function_exists('prevent_direct_access') ) :
function prevent_direct_access ($allow) {
	$ref = getenv('HTTP_REFERER');
	if (!$ref || $ref != $allow) {
		header("HTTP/1.0 403 Forbidden");	
		die("Access denied");
	}
}
endif;
// $product['product_price_mx'], $product['product_price_mx_offer'], $product['product_featured'], $product['product_featured_end'])
if ( !function_exists('choose_price') ) :
function choose_price ($ppm, $ppmo, $pf, $pfe) {
	if ($pf) {
		if ($ppmo < 1)
			return $ppm;
		return (strtotime(today_mysql_datetime()) < strtotime($pfe)) ? $product['product_price_mx_offer'] : $product['product_price_mx'];
	}
	else
		return $ppm;
}
endif;

// Receive buy price and calculate the new public price Apply to: Edit Product and Product Page
if ( !function_exists('load_price_from_pt') ) :
function load_price_from_pt ($cpt, $pp) {
	$e = explode (",", $cpt);
	foreach ($e as $r)
		list($from[], $to[], $p[]) = explode ("-",$r);
	$number = ( (int) trim(str_replace(",", "", $pp ) ) ) * 1.16;
	foreach ($p as $k => $v)
		if ($number >= $from[$k] && $number < $to[$k])
			break;
	return round($number * ($p[$k] / 100 + 1), 2);
}
endif;

// Return Categories List for Add File Module
if ( !function_exists('product_related_table') ) :
function product_related_table ($module, $name, $table, $select = 0) {
$module="add-product";
	switch ($table) {
		// case 'products': $column = "product_user"; break;
		case 'categories': $column = "category_status"; $dd_name = "category"; break;
		case 'brands': $column = "brand_status"; $dd_name = "brand"; break;
	}
	$sql = "SELECT * FROM $table WHERE $column = 1";
	if ($dd_name == "category")
		$sql .= " ORDER BY category_order";
		// $sql .= " AND category_parent!= 0 ORDER BY category_order";
	else
		$sql .= " ORDER BY $dd_name" . "_name ASC";
	// exit ($sql);
	$result = mysql_query($sql);	
	$dd_related_table = '<select name="'.$name.'" id="'.$name.'"><option value="0">-- Ninguna --</option>';
	$last_item_type = 1;
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if (($module == 'edit-product' || $module == 'add-product' || $module == 'add-product2') && $table == 'categories' && !$row['category_parent'] && $last_item_type) {
			$dd_related_table .= '<optgroup label="'. stripslashes($row['category_name']) .'">';
			continue;
		}
		if ( isset($row['category_parent']) && (int) $row['category_parent'] && $module == 'edit-category') continue;
		$dd_related_table .= '<option value="'.$row[$dd_name . '_id'].'"'.(($row[$dd_name . '_id'] == $select) ? " selected='selected'" : "").'>'.stripslashes($row[$dd_name . '_name']) . "</option>\n";
		if (($module == 'edit-product' || $module == 'add-product') && $table == 'categories' && $row['category_parent'] && !$last_item_type)
			$dd_related_table .= "</optgroup>\n";
		if ( isset($row['category_parent']) )
			$last_item_type = $row['category_parent'];
	}
	return $dd_related_table . "</select>";
}
endif;
// Return especified Table with all its rows.
if ( !function_exists('table') ) :
function table ($table) {
	$sql = "SELECT * FROM $table";
	$result = mysql_query($sql);
	return $result;
}
endif;
// Return field from id, table and column especified
if ( !function_exists('row_field_table') ) :
function row_field_table ($table, $column, $field, $key) {
	$sql = "SELECT * FROM $table WHERE $column = '$key'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return (empty($field)) ?  $row : $row[$field];
}
endif;
// Count total rows from table specified
if ( !function_exists('count_table_rows') ) :
function count_table_rows ($table) {
	$temp = mysql_query("SELECT SQL_CALC_FOUND_ROWS * FROM $table LIMIT 1");
	$result = mysql_query("SELECT FOUND_ROWS()");
	$total = mysql_fetch_row($result);
	return $total[0];
}
// Count Entries for Verify Exist Purposes
if ( !function_exists('count_entries') ) :
function count_entries ($table, $column, $key) {
	$temp = mysql_query("SELECT SQL_CALC_FOUND_ROWS * FROM $table WHERE $column = '$key' LIMIT 1");
	$result = mysql_query("SELECT FOUND_ROWS()");
	$total = mysql_fetch_row($result);
	return $total[0];
}
endif;

if ( !function_exists('products_counter') ) :
function products_counter ($module) {
	switch ($module) {
		case 'products': $column = "product_user"; break;
		case 'categories': $column = "product_category"; break;
		case 'brands': $column = "product_brand"; break;
	}
	// $column = ($module == "products") ? "product_user" : "product_category";
	$products_counter = array();
	$table_products = table ("products");
	while($file = mysql_fetch_array($table_products, MYSQL_BOTH)) {
		$products_counter [$file[$column]] = (!array_key_exists($file[$column], $products_counter))  ? 1 : $products_counter [$file[$column]] + 1;
	}
	return $products_counter;
}
endif;

if ( !function_exists('attributes') ) :
function attributes ($category) {
	$attributes = array();
	$sql = "SELECT * FROM attributes WHERE att_status = 1 AND att_category = " . $category . " ORDER BY att_order";
	$result = mysql_query($sql);
	$nrows = ($result) ? (int) mysql_num_rows($result) : 0;
	if ($nrows):
		$attributes = array();
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)):
			if (!(int) $row['att_parent'])
				$attributes[$row['att_id']] = $row;
			else
				$attributes[$row['att_parent']]['attributes'][$row['att_id']] = $row;
		endwhile;
	endif;
	return $attributes;
}
endif;

if ( !function_exists('options_menu') ) :
function options_menu ($fname) {
	global $t;
	$menu_items = array (
		"dashboard"		=> 	array (),		
		"products"		=> 	array ("add-product", "add-product2"),
		"brands"		=> 	array ("add-brand", "edit-brand"),
		"categories"	=> 	array ("add-category", "edit-category", "organize-categories"),
		"users"			=> 	array ("add-user", "edit-user")
		//"trash"			=> 	array (),
	);
	$keys_menu_items = array_keys ($menu_items);		
	if (in_array($fname, $keys_menu_items))
		$active_option_menu = $fname;
	else {
		for($i=0; $i < count ($keys_menu_items); $i++)
			if (in_array($fname, $menu_items[$keys_menu_items[$i]]))
				break;
		$active_option_menu = $keys_menu_items[$i];
	}	
	foreach ($keys_menu_items as $m) {
		$current_class = ($active_option_menu == $m) ? ' class="current"' : '';		
		echo '<li'.$current_class.'><a href="'.$m.'" title="'.$t[$m].'">'.$t[$m].'</a></li>';
	}
}
endif;

if ( !function_exists('application_menu') ) :
function application_menu ($fname) {
	global $t, $user;
	// exit ($user['user_caps'] . "<br />" . $fname);
	$menu_items = array (
		"dashboard"		=> 	array (),		
		"products"		=> 	array ("update", "add-product", "add-product2", "edit-product", "edit-product-min", "mkt", "stock"),
		"brands"		=> 	array ("add-brand", "edit-brand"),
		"categories"	=> 	array ("add-category", "edit-category", "pt", "organize-categories"),
		"users"			=> 	array ("add-user", "edit-user"),
		"clients"		=> 	array ("edit-account", "edit-client", "edit-order", "orders", "add-order"),
		"trash"			=> 	array (),
		"tools"			=> 	array ("sitemap", "stats", "mistakes", "campaign")
	);
	$submenu_items = array (
		"dashboard"		=> 	array (),
		"products"		=> 	array ("update", "add-product", "add-product2", "mkt", "stock"),
		"brands"		=> 	array ("add-brand"),
		"categories"	=> 	array ("add-category", "organize-categories"),
		"users"			=> 	array ("add-user"),
		"clients"		=> 	array ("orders", "add-order"),
		"trash"			=> 	array (),
		"tools"			=> 	array ("sitemap", "stats", "mistakes", "campaign")
	);	
	$keys_menu_items = array_keys ($menu_items);		
	if (in_array($fname, $keys_menu_items))
		$active_option_menu = $fname;
	else {
		for($i=0; $i < count ($keys_menu_items); $i++)
			if (in_array($fname, $menu_items[$keys_menu_items[$i]]))
				break;
		$active_option_menu = $keys_menu_items[$i];
	}	
	foreach ($keys_menu_items as $m) {
		if (!$user['user_level'])
			if (!in_array($m,explode(',', $user['user_caps'])))
				continue;		
		$current_class = ($active_option_menu == $m) ? ' class="current"' : '';
		echo '<li'.$current_class.'><a'.$current_class.' href="'.$m.'" title="'.$t[$m].'">'.$t[$m].'</a>';
		if (!empty($submenu_items[$m])) {
			echo '<ul style="display: none;" class="submenu">';
			foreach ($submenu_items[$m] as $smi) {
				if (!$user['user_level'])
					if (!in_array($smi,explode(',', $user['user_caps'])))
						continue;
				echo '<li><a href="' . $smi .'.php">' . $t[$smi]. '</a></li>';
			}
			echo  '</ul>';
		}
		echo  '</li>';
	}
}
endif;




// DropDown Level Access User
if ( !function_exists('dropdown_user_level') ) :
function dropdown_user_level ($selected = 0) {
	global $euid;
	$dd[0] = $euid['user_language'] == 'es' ? "Usuario":"User";
	$dd[1] = $euid['user_language'] == 'es' ? "Administrador":"Manager";
	for ($i=0; $i < 2; $i++) {
		$select = ($i == $selected) ? " selected='selected'" : "";
		echo "<option value='$i'$select>".$dd[$i]."</option>";
	}
}
endif;

// DropDown Language User
if ( !function_exists('dropdown_lang') ) :
function dropdown_lang ($selected = 0) {
	$dd = array(0 => "Espa&ntilde;ol", 1 => "English",);
	for ($i=0; $i < 2; $i++) {
		$select = ($i == $selected) ? " selected='selected'" : "";
		echo "<option value='$i'$select>".$dd[$i]."</option>";
	}
}
endif;

// Input Lenght
if ( !function_exists('input_lenght') ) :
function input_lenght ($str) {
	$str_lenght = strlen (stripslashes($str));
	return ($str_lenght < 50) ? 45 : 90;
}
endif;

// DropDown Themes from themes folder
if ( !function_exists('dropdown_templates') ) :
function dropdown_templates($current) {
	$path = "themes";
	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			if (is_file($path."/".$file))
				$files[] = $file;
		}
		closedir($handle);
	}
	foreach($files as $f) {
		$select = ($f == $current) ? " selected='selected'" : "";
		echo "<option value='$f'$select>".ucfirst(path_info($f, "filename"))."</option>";
	}	
}
endif;

/* Gets the data from a URL */
if ( !function_exists('get_remote_data') ) :
function get_remote_data($url) {
	$ch = curl_init();
	$timeout = 0;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
endif;

function download_remote_data($url, $local_path, $filename) {
    $fp = @fopen($local_path.$filename, "wb");
    if (!$fp)
		exit;   
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FILE, $fp);               
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}

// Meta Redirect
if ( !function_exists('meta_redirect') ) :
function meta_redirect ($style, $text, $url) {
	exit('<div id="'.$style.'">'.$text.'</div><META HTTP-EQUIV="Refresh" Content="3; URL='.$url.'">');
}
endif;

// F I L E S FUNCTIONS //
// Write a variable to file
if ( !function_exists('var_toFile') ) :
function var_toFile ($file, $var) {
	if ($handle = @fopen($file, "w+")) {
		fwrite($handle, $var);
		fclose($handle);
		return TRUE;
	}
	return FALSE;
}
endif;

// - Convert e.g. 201575 (bytes) to 196.85 KB
if ( !function_exists('ufilesize') ) :
function ufilesize($size) {
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	while ($size >= 1024) {
		   $size /= 1024;
		   $pos++;
	}
	return round($size,2)." ".$a[$pos];
}
endif;

// Get the Extension from a file name.
if ( !function_exists('getExtension') ) :
function getExtension($string) {
   $pos = false;
   $search = ".";
   if (is_int(strpos($string, $search))) {
	   $endPos = strlen($string);
	   while ($endPos > 0) {
		   $endPos = $endPos - 1;
		   $pos = strpos($string, $search, $endPos);
		   if (is_int($pos)) {
			   break;
		   }
	   }
   }
   if (is_int($pos)) {
	   $len = strlen($search);
	   return substr($string, $pos);
   }
	return $string;
}
endif;

// Extract string between strings with a patron asigned. e.g.: [[5]]  => 5
if ( !function_exists('extract_between') ) :
function extract_between($TheStr, $sLeft, $sRight){
        $pleft = strpos($TheStr, $sLeft, 0);
        if ($pleft !== false){
                $pright = strpos($TheStr, $sRight, $pleft + strlen($sLeft));
                if ($pright !== false) {
                        return (substr($TheStr, $pleft + strlen($sLeft), ($pright - ($pleft + strlen($sLeft)))));
                }
        }
        return '';
}
endif;

// Get the Extension from a file name.
if ( !function_exists('get_file_extension') ) :
function get_file_extension($file) {
	$fileparts = explode(".", $file);
	return end($fileparts);
}
endif;

// Remove folder with its files (php.net).
if ( !function_exists('remove_recursive_directory') ) :
function remove_recursive_directory($dir, $DeleteMe) {
    if(!$dh = @opendir($dir)) return;
    while (false !== ($obj = readdir($dh))) {
        if($obj=='.' || $obj=='..') continue;
        if (!@unlink($dir.'/'.$obj)) remove_recursive_directory($dir.'/'.$obj, true);
    }
    closedir($dh);
    if ($DeleteMe){
        @rmdir($dir);
    }
}
endif;

// Download File
if ( !function_exists('output_file') ) :
function output_file($file, $name, $mime_type) { 
	$size = filesize($file);
	$name = rawurldecode($name);

	@ob_end_clean(); //turn off output buffering to decrease cpu usage

	// required for IE, otherwise Content-Disposition may be ignored
	if(ini_get('zlib.output_compression'))
	ini_set('zlib.output_compression', 'Off');

	header('Content-Type: ' . $mime_type);
	header('Content-Disposition: attachment; filename="'.$name.'"');
	header("Content-Transfer-Encoding: binary");
	header('Accept-Ranges: bytes');

	// The three lines below basically make the download non-cacheable
	header("Cache-control: private");
	header('Pragma: private');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 
	// multipart-download and download resuming support
	if(isset($_SERVER['HTTP_RANGE'])) {
		list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
		list($range) = explode(",",$range,2);
		list($range, $range_end) = explode("-", $range);
		$range=intval($range);
		if(!$range_end) {
			$range_end=$size-1;
		} else {
			$range_end=intval($range_end);
		}
		$new_length = $range_end-$range+1;
		header("HTTP/1.1 206 Partial Content");
		header("Content-Length: $new_length");
		header("Content-Range: bytes $range-$range_end/$size");
	} else {
		$new_length=$size;
		header("Content-Length: ".$size);
	}
 
	/* output the file itself */
	$chunksize = 1*(1024*1024); //you may want to change this
	$bytes_send = 0;
	if ($file = fopen($file, 'r')) {
		if(isset($_SERVER['HTTP_RANGE']))
		fseek($file, $range);

		while(!feof($file) && (!connection_aborted()) && ($bytes_send<$new_length)) {
			$buffer = fread($file, $chunksize);
			print($buffer); //echo($buffer); // is also possible
			flush();
			$bytes_send += strlen($buffer);
		}
		fclose($file);
	} else die('Error - can not open file.');
	die();
}
endif;

/* Returns information about a file path like: /www/htdocs/index.html
	dirname => /www/htdocs
	basename => index.html
	extension => html
	filename => index
*/
if ( !function_exists('path_info') ) :
function path_info ($path, $info) {
	$path_parts = pathinfo($path);
	return $path_parts[$info];
}
endif;

if ( !function_exists('explodeSkus') ) :
function explodeSkus ($skus, $link_provider) {
	$str_skus = "";
	$skus = explode(",", $skus);
	foreach ($skus as $sk)
		$str_skus .= (str_replace("STR_REPLACE", trim($sk), '<a href="'.$link_provider.'" title="P&aacute;gina del Producto" target="_blank">'.trim($sk).'</a> '));
	return $str_skus;
}
endif;

/* S T R I N G   M A N I P U L A T I O N FUNCTIONS */
/****************************************/
// TRANSFORM SPECIAL CHARACTERS AS ACCENTS TO HTML ENTITIES, EXCEPT: <, & AND >
// Received: El Tráfico: Madrid colapsado por las obras
// Returned: El Tr&aacute;fico: Madrid colapsado por las obras
if ( !function_exists('characters_html') ) :
function characters_html($str){
      $str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
      $str = htmlspecialchars_decode($str, ENT_NOQUOTES);
      return $str;
}
endif;

// TRANSFORM NORMAL TEXT WITH PUNCTUATION MARKS & ACCENTS TO SEO STR
// String Received: El Tráfico: Madrid colapsado por las obras
// String Returned: el-trafico-madrid-colapsado-por-las-obras
if ( !function_exists('str_to_seo') ) :
function str_to_seo($str) {
	if ( @preg_match ('/.+/u', $str) )
		$str = utf8_decode($str);
	$str = htmlentities($str);
	$str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde);/','$1',$str);
	$str = html_entity_decode($str);
	$str = strtolower($str);	
	$str_parts = split("[ ()/']",trim($str));
	$parts_destinity = array();
	foreach($str_parts as $part) {
		if (!empty($part)) {
			$part = trim($part);
			$part = ereg_replace("[^a-z0-9]", "", $part);
			$parts_destinity[] = $part;
		}
	}
	$str = implode("-",$parts_destinity);
	$str = ($str);
	return $str;
}
endif;

// Return unix time for file name
if ( !function_exists('str_utime') ) :
function str_utime () {
	return str_replace(".", "", implode("",array_reverse(explode(" ",microtime()))));
}
endif;

// Return string name for file name
function str_file_name ($name){
	$utime = str_utime ();
	$ext = get_file_extension ($name);
	return  $utime . "." . $ext ;
}
endif;

// VALIDATIONS FUNCTIONS
if ( !function_exists('is_valid_domain') ) :
function is_valid_domain($domain) {
	$da = @fsockopen($domain, 80, $errno, $errstr, 5);
	if ($da)
	{	fclose($da);
		return TRUE;		
	}
	return FALSE;
}
endif;

// Verify that $array doesn't contain empty fields
if ( !function_exists('is_array_full') ) :
function is_array_full ($array) {	
	if (!is_array($array))
		return FALSE;
	foreach($array as $a)
		if (empty($a))
			return FALSE;
	return TRUE;
}
endif;

// Return $errros array formated.
if ( !function_exists('array_errors') ) :
function array_errors ($errors) {
	global $user;	
	$errors = array_unique($errors);
	$errors_list = '<div id="error"><strong>';
	$errors_list .= $user['user_language'] == 'en' ? 'The following error(s) have occured' : 'Han ocurrido los siguientes errores';
	$errors_list .= ':</strong><br />';
	foreach ($errors as $e)
		$errors_list .= "&bull; $e<br/>";
	$errors_list .= "</div>";
	return $errors_list;
}
endif;

if(!function_exists('how_long_ago')){
	function how_long_ago($timestamp){
		if ($timestamp == "943941600") return "";
		$difference = time() - $timestamp;
		if($difference >= 60*60*24*365){        // if more than a year ago
			$int = intval($difference / (60*60*24*365));
			$s = ($int > 1) ? 's' : '';
			$r = 'hace ' . $int . ' a&ntilde;o' . $s;
		} elseif($difference >= 60*60*24*7*5){  // if more than five weeks ago
			$int = intval($difference / (60*60*24*30));
			$s = ($int > 1) ? 'es' : '';
			$r = 'hace ' . $int . ' mes' . $s;
		} elseif($difference >= 60*60*24*7){        // if more than a week ago
			$int = intval($difference / (60*60*24*7));
			$s = ($int > 1) ? 's' : '';
			$r = 'hace ' . $int . ' semana' . $s;
		} elseif($difference >= 60*60*24){      // if more than a day ago
			$int = intval($difference / (60*60*24));
			$s = ($int > 1) ? 's' : '';
			$r = 'hace ' . $int . ' d&iacute;a' . $s;
		} elseif($difference >= 60*60){         // if more than an hour ago
			$int = intval($difference / (60*60));
			$s = ($int > 1) ? 's' : '';
			$r = 'hace ' . $int . ' hora' . $s;
		} elseif($difference >= 60){            // if more than a minute ago
			$int = intval($difference / (60));
			$s = ($int > 1) ? 's' : '';
			$r = 'hace ' . $int . ' minuto' . $s;
		} else {                                // if less than a minute ago
			$r = 'hace unos segundos';
		}

		return $r;
	}
}

// Validate date es: dd/mm/aaaa en: mm/dd/aaaa
if ( !function_exists('is_valid_date') ) :
function is_valid_date($date, $loc) {
	$pattern = ($loc == "es") ? '/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/(19|20)[0-9]{2}$/' : '/^(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])\/(19|20)[0-9]{2}$/';
	return (!preg_match($pattern, $date)) ? FALSE : TRUE;
}

function is_valid_url($str) {
	return (!preg_match("/^http:\/\/[a-z0-9-]{1,}?\.?[a-z0-9-]*\.?[a-z0-9]{3}?.[a-z]{2,}(\/[a-z0-9-])?\/?$/i", $str)) ? FALSE : TRUE;
}
function is_valid_email($str) {
	return (strlen($str) > 50 || !preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
}

// Turn datepicker date (es: dd/mm/aaaa en: mm/dd/aaaa) to mysql format.
if ( !function_exists('datepicker_to_mysql') ) :
function datepicker_to_mysql ($datepicker, $t) {
	$date = str_replace("/", "-", $datepicker);
	$date = explode("-", $date);
	$date = (is_valid_date($datepicker, "es")) ? $date[2]."-".$date[1]."-".$date[0]." ". $t : $date[2]."-".$date[0]."-".$date[1]." ". $t;
	return $date;
}
endif;

// Turn mysql date to datepicker format (es: dd/mm/aaaa en: mm/dd/aaaa)
if ( !function_exists('mysql_to_datepicker') ) :
function mysql_to_datepicker ($mysql, $lang) {	
	list($date, $time) = explode(' ', $mysql);
	list($year, $month, $day) = explode('-', $date);		
	$datepicker = ($lang == "es") ? $day . "/" . $month . "/" . $year : $month . "/" . $day . "/" . $year;
	return $datepicker;
}
endif;

// Return current date in mysql format
if ( !function_exists('today_mysql_datetime') ) :
function today_mysql_datetime () {
	return date("Y-m-d H:i:s"); 
}
endif;

// Function to turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into a unix timestamp
if ( !function_exists('convert_datetime_timestamp') ) :
function convert_datetime_timestamp($str) {
	list($date, $time) = explode(' ', $str);
	list($year, $month, $day) = explode('-', $date);
	list($hour, $minute, $second) = explode(':', $time);
	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
	return $timestamp;
}
endif;

// Function to turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into normal date * @param date(Y-m-d) $fecha * @return date(d/m/Y)
if ( !function_exists('date_mysql_normal') ) :
function date_mysql_normal($full_date, $lang = "es", $full = FALSE){
	//Esta función convierte la fecha del formato DATETIME de SQL a formato según el lenguaje recibido 
	$mn = array('Ene.', 'Feb.', 'Mar.', 'Abr.', 'May.', 'Junio', 'Julio', 'Ago.', 'Sep.', 'Oct.', 'Nov.', 'Dic.');
	$date_time = explode(" ", $full_date);
	list($hour, $min, $sec) = explode(":", $date_time[1]);
	list($year, $month, $day) = explode("-", $date_time[0]);
	$month = $lang == "es" ? $mn[(int)$month -1]: strftime("%B", strtotime($date_time[0]));
	// $converted = $lang == "es" ? $day . " de ". $month . " de " : $month . " " . $day . ", "; // ORIGINAL
	$converted = $lang == "es" ? $day . " de ". $month : $month . " " . $day . ", ";
	$converted = $full ? date('j M Y h:i:s a', convert_datetime_timestamp($full_date)) : $converted;
	// $converted .= $full ? $year. ". " .  ($hour>12? $hour-12 : $hour) . ":" . $min . date("a", $date_time[1]) : "";
	// $converted .= $full ? $year. ". " .  ($hour>12? $hour-12 : $hour) . ":" . $min . date("a", $date_time[1]) : $year . ". "; // ORIGINAL
	//$converted = $date.' '.$date_time[1];
 	//return $converted;
	return $converted;
}
endif;

/*** <head></head> FUNCTIONS ***/
//if ( !function_exists('fname_title') ) :
function fname_title () {
	global $t;
	$path = pathinfo ($_SERVER['PHP_SELF']);
	$title = isset($t[$path['filename']]) ? $t[$path['filename']] : $path['filename'];	
	return array($path['filename'], $title);
}
endif;

if ( !function_exists('tooltip') ) :
function tooltip ($content) {
	return '<img onmouseover="tooltip.show(\''.addslashes($content).'\');" onmouseout="tooltip.hide();" src="images/information.png" width="16" height="16" />';
}
endif;

// Extract patterns
if ( !function_exists('extract_patterns') ) :
function extract_patterns($TheStr, $sLeft, $sRight){
    $patron = "/(".$sLeft.")(.+?)(".$sRight.")/sie";
    preg_match_all($patron, $TheStr, $matches);
    return $matches[0];
}
endif;
// Return string contains scripts for include between head tags
if ( !function_exists('add_scripts_styles_usr') ) :
function add_scripts_styles_usr($module){
	$resource_list = "";
	list($resources) = scripts_styles_usr ($module);
	if (is_array($resources))
		for ($i = 0; $i < count ($resources); $i++)
			$resource_list .= (getExtension($resources[$i]) == ".js") ?
			'<script type="text/javascript" src="/min/f='.$resources[$i].'"></script>' . "\n" : 
			'<link href="/min/f='.$resources[$i].'" rel="stylesheet" type="text/css" />' . "\n";
	return rtrim($resource_list);
}
endif;

if ( !function_exists('scripts_styles_usr') ) :
function scripts_styles_usr ($module) {
	$common = array (
		"css/new.css",
		"css/common.css"
	);
	$resources = array (
		"home" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/stepcarousel_newest.js",
			),
		"product" =>
			array (
				"css/search.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js"
			),
		"brand" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/jquery.lazyload.mini.js",
				"scripts/jquery.liveFilter.js"
			),
		"category" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/jquery.lazyload.mini.js",
				"scripts/quickpager.jquery.js",
				"scripts/jquery.liveFilter.js"
			),
		"offers" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/jquery.lazyload.mini.js",
				"scripts/quickpager.jquery.js",
				"scripts/jquery.liveFilter.js"
			),
		"buscar" =>
			array (
				"css/search.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/jquery.lazyload.mini.js",
				"scripts/jquery.liveFilter.js"
			),
		"desarrollo" =>
			array (
				"phpmailer/contact.css",
				"css/jquery.fancybox-1.3.0.css",
				"css/desarrollo-web.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",				
				"scripts/jquery.fancybox-1.3.0.pack.js",
				"scripts/jquery.simplespy.js",
				"scripts/jquery.idTabs.min.js",				
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/cmxforms.js",
				"scripts/contacto.js"
				
			),
		"dominios" =>
			array (
				"css/table.css",
				"phpmailer/contact.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/style-table.js",				
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/cmxforms.js",
				"scripts/contacto.js"				
			),
		"hospedaje" =>
			array (
				"css/table.css",
				"phpmailer/contact.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/style-table.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/cmxforms.js",
				"scripts/contacto.js"
			),
		"contacto" =>
			array (
				"phpmailer/contact.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/cmxforms.js",
				"scripts/contacto.js"				
			),
		"somos" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
			),
		"terminos" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
			),
		"privacidad" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
			),
		"mantenimiento" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
			),
		"promociones" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
			),
		"productos" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
			),
		"jugar" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
			),
		"configurador-kingston" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
			),
		"clientes" =>
			array (
				"css/shoppingCart.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",				
				"scripts/pwstrength.js",
				"scripts/contacto.js",
				"scripts/clients.js"
			),
		"pedido" =>
			array (
				"css/shoppingCart.css",
				"scripts/jquery-1.3.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/jquery-ui-1.7.2.min.js",
				"scripts/jquery-ui-1.7.2.dialog.min.js",
				"css/hot-sneaks/jquery-ui-1.7.1.custom.css",
				"scripts/pedido.js"
			)
	);	
	return array (array_merge($common, $resources[$module]));
}
endif;

// Return string contains scripts for include between head tags
if ( !function_exists('add_scripts_styles') ) :
function add_scripts_styles($module){
	global $user;
	$resource_list = "<link href=\"css/styles.css\" rel=\"stylesheet\" type=\"text/css\" />\n<link href=\"css/common.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
	list($resources) = scripts_styles ($module);
	if (is_array($resources))
		for ($i = 0; $i < count ($resources); $i++)
			$resource_list .= (getExtension($resources[$i]) == ".js") ?
			"<script type=\"text/javascript\" src=\"".$resources[$i]."\"></script>\n" :
			"<link href=\"".$resources[$i]."\" rel=\"stylesheet\" type=\"text/css\"/>\n";
	if ($user['user_language'] == "es" && ($module == "add-product" ||$module == "add-product2" || $module == "edit-product" || $module == "orders" || $module == "edit-product-min"))
		$resource_list .="<script type=\"text/javascript\" src=\"scripts/jquery-ui.datepicker-es.js\"></script>\n";
	return rtrim($resource_list);
}
endif;

if ( !function_exists('scripts_styles') ) :
function scripts_styles ($module) {
	$resources = array (
		"add-category" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/add-category.js"
			),
		"update" =>
			array (
				"scripts/jquery-1.6.2.min.js",
				"scripts/jquery-ui-1.7.1.min.js",
				"scripts/jquery-ui.dialog.min.js",
				"css/hot-sneaks/jquery-ui-1.7.1.custom.css",
				"scripts/bundle_common.js",
				"scripts/update.js"
			),
		"add-product" =>
			array (
				"scripts/jquery-1.3.2.min.js",				
				"scripts/jquery-ui-1.7.1.min.js",
				"css/hot-sneaks/jquery-ui-1.7.1.custom.css",
				"scripts/jquery-ui.datepicker.min.js",
				"scripts/jquery.blockUI.js",				
				"scripts/tiny_mce/tiny_mce.js",
				"scripts/reactivateSession.js",
				"scripts/add-product.js"
			),
			"add-product2" =>
			array (
				"scripts/jquery-1.3.2.min.js",		
				"scripts/jquery-ui-1.7.1.min.js",
				"css/hot-sneaks/jquery-ui-1.7.1.custom.css",
				"scripts/jquery-ui.datepicker.min.js",
				"scripts/jquery.blockUI.js",				
				"scripts/tiny_mce/tiny_mce.js",
				"scripts/reactivateSession.js",
				"scripts/add-product.js",
				"prueba-ajax/jquery.js",
				"prueba-ajax/style/jquery.fancybox-1.3.4.css",
				"prueba-ajax/style/jquery.fancybox-1.3.4.pack.js",
				
			),
		"edit-product" =>
			array (
				"scripts/jquery-1.8.2.js",
				"scripts/jquery-ui-1.9.1.custom.min.js", // Include: UI Core, Dialog & Datepicker
				"css/hot-sneaks-2/jquery-ui-1.9.1.custom.min.css",
				"scripts/jquery.blockUI.js",
				"scripts/tiny_mce/tiny_mce.js",
				"scripts/reactivateSession.js",
				"scripts/edit-product.js"
			),			
		"edit-product-min" =>
			array (
				"scripts/jquery-1.8.2.js",
				"scripts/jquery-ui-1.9.1.custom.min.js", // Include: UI Core, Dialog & Datepicker
				"css/hot-sneaks-2/jquery-ui-1.9.1.custom.min.css",
				"scripts/jquery.blockUI.js",
				"scripts/tiny_mce/tiny_mce.js",
				"scripts/reactivateSession.js",
				"scripts/edit-product-min.js"
			),			
		"edit-category" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery-ui-1.7.1.min.js",
				"scripts/jquery-ui.dialog.min.js",
				"css/cupertino/jquery-ui-1.7.2.custom.css",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",				
				"scripts/jquery.blockUI.js",
				"scripts/interface.js",
				"scripts/cmxforms.js",
				"scripts/jquery.jeditable.mini.js",				
				"scripts/edit-category.js"			
			),			
		"add-user" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/add-user.js"				
			),
		"create-account" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/pwstrength.js",
				"scripts/contacto.js",
				"css/clients.css"				
			),
		"edit-account" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/pwstrength.js",
				"scripts/contacto.js",
				"css/clients.css"				
			),	
		"brands" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/module-brands.js",
				"scripts/tooltip.js",
				"css/tooltip.css"
			),
		"add-brand" =>
			array (
				"scripts/jquery-1.3.2.min.js",				
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",				
				"scripts/add-brand.js"
			),			
		"categories" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/module-categories.js",
				"scripts/tooltip.js",
				"css/tooltip.css"
			),
		"dashboard" =>
			array (
				"scripts/jquery-1.3.2.min.js",				
				"scripts/jquery-ui-1.7.1.min.js",
				"scripts/jquery-ui.dialog.min.js",
				"scripts/dashboard.js",
				"css/hot-sneaks/jquery-ui-1.7.1.custom.css"
			),
		"mkt" =>
			array (
				"scripts/jquery-1.3.2.min.js"
			),
		"stats" =>
			array (
				"scripts/jquery-1.3.2.min.js"
			),
		"tools" =>
			array (
				"scripts/jquery-1.3.2.min.js"
			),
		"products" =>
			array (
				"scripts/jquery-1.3.2.min.js",				
				"scripts/jquery-ui-1.7.1.min.js",
				"scripts/jquery.liveFilter.js",
				"scripts/jquery-ui.dialog.min.js",				
				"scripts/jquery.history.js",
				"scripts/easing.js",
				"scripts/jquery.tablesorter.min.js",
				"scripts/module-products.js",
				"css/cupertino/jquery-ui-1.7.2.custom.css"				
			),
		"pt" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/pt.js"
			),
		"stock" =>
			array (
				"scripts/jquery-1.3.2.min.js"		
			),			
		"users" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery-ui-1.7.2.min.js",
				"scripts/jquery-ui-1.7.2.dialog.min.js",
				"css/cupertino/jquery-ui-1.7.2.custom.css",
				"scripts/module-users.js"				
			),
		"clients" =>
			array (
				"scripts/jquery-1.3.2.min.js",				
				"scripts/jquery-ui-1.7.1.min.js",
				"scripts/jquery-ui.dialog.min.js",
				"css/hot-sneaks/jquery-ui-1.7.1.custom.css",				
				"scripts/module-clients.js"				
			),
		"orders" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery-ui-1.7.2.min.js",
				"scripts/jquery.liveFilter.js",
				"scripts/jquery-ui.dialog.min.js",
				"scripts/jquery-ui.datepicker.min.js",				
				"scripts/jquery.history.js",
				"scripts/easing.js",
				"scripts/jquery.tablesorter.min.js",
				"css/hot-sneaks/jquery-ui-1.8.9.custom.css",
				"scripts/module-orders.js"
			),
		"add-order" =>
			array (
				"css/shoppingCart.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/jquery-ui-1.8.9.custom.min.js",
				"scripts/jquery.ui.combogrid-1.6.2.js",
				"css/hot-sneaks/jquery-ui-1.7.1.custom.css",
				"css/jquery.ui.combogrid.css",				
				"scripts/bundle_common.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/add-order.js",
				"scripts/clients.js"
			),
		"webmessenger" =>
			array (
				"scripts/jquery-1.3.2.min.js"
			),			
		"trash" =>
			array (
				"scripts/jquery-1.3.2.min.js",				
				"scripts/jquery-ui-1.7.1.min.js",
				"scripts/jquery.liveFilter.js",
				"scripts/jquery-ui.dialog.min.js",				
				"scripts/jquery.history.js",
				"scripts/easing.js",
				"scripts/jquery.tablesorter.min.js",
				"scripts/module-products.js",
				"css/cupertino/jquery-ui-1.7.2.custom.css"				
			),
		"sitemap" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/edit-user.js"
			),
		"campaign" =>
			array (
				"scripts/jquery-1.8.2.js",
				"scripts/jquery-ui-1.9.1.custom.min.js", // Include: UI Core, Dialog & Datepicker
				"css/hot-sneaks-2/jquery-ui-1.9.1.custom.min.css",
				"scripts/jquery.blockUI.js",
				"scripts/campaign.js"
			),
		"notifications" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/notifications.js"
			),
		"mistakes" =>
			array (
				"scripts/jquery-1.3.2.min.js"
			),			
		"edit-brand" =>
			array (
				"scripts/jquery-1.3.2.min.js",				
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",				
				"scripts/edit-brand.js"			
			),				
		"organize-categories" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery-ui-1.7.2.min.js",
				"scripts/jquery.blockUI.js",
				"scripts/interface.js"
			),
		"organize-attributes" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery-ui-1.7.2.min.js",
				"scripts/jquery.blockUI.js",
				"scripts/interface.js"
			),
		"edit-order" =>
			array (
				"css/shoppingCart.css",
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery-ui-1.7.2.min.js",
				"scripts/jquery-ui.dialog.min.js",
				"css/hot-sneaks/jquery-ui-1.7.1.custom.css",
				"scripts/jquery-ui.datepicker.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/pedido.js",
				"scripts/edit-order.js",
				"scripts/jquery-ui.datepicker-es.js"
			),
		"edit-user" =>
			array (
				"scripts/jquery-1.3.2.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/edit-user.js",				
				"scripts/style-switcher.js"				
			),
		"edit-client" =>
			array (
				"css/shoppingCart.css",
				"scripts/jquery-1.6.2.min.js",
				"scripts/bundle_common.js",
				"scripts/jquery.idTabs.min.js",
				"scripts/jquery.form.js",
				"scripts/jquery.validate.js",
				"scripts/jquery.blockUI.js",
				"scripts/cmxforms.js",
				"scripts/pwstrength.js",
				"scripts/contacto.js",
				"scripts/clients.js"
			)
	);	
	return array ($resources[$module]);
}
endif;
// If you want testing host availability, by Mat
// From: http://www.matinfo.ch/blog/archive/2006/02/22/test-host-availability.html
if ( !function_exists('hostLive') ) :
function hostLive($host) {
    $ckhost = @fsockopen($host, 80, $errno, $errstr, 30);
    return ($ckhost) ? 1 : 0;
}
endif;

if ( !function_exists('normalize') ) :
	function normalize ($string) {
		$table = array(
			'Š'=>'S',
			'š'=>'s',
			'Ð'=>'Dj',
			'Ž'=>'Z',
			'ž'=>'z',
			'À'=>'A', 
			'Á'=>'A',
			'Â'=>'A',
			'Ã'=>'A', 
			'Ä'=>'A', 
			'Å'=>'A', 
			'Æ'=>'A', 
			'Ç'=>'C', 
			'È'=>'E', 
			'É'=>'E',
			'Ê'=>'E', 
			'Ë'=>'E', 
			'Ì'=>'I', 
			'Í'=>'I', 
			'Î'=>'I', 
			'Ï'=>'I', 
			'Ñ'=>'N', 
			'Ò'=>'O', 
			'Ó'=>'O', 
			'Ô'=>'O',
			'Õ'=>'O', 
			'Ö'=>'O', 
			'Ø'=>'O', 
			'Ù'=>'U', 
			'Ú'=>'U', 
			'Û'=>'U', 
			'Ü'=>'U', 
			'Ý'=>'Y', 
			'Þ'=>'B', 
			'ß'=>'Ss',
			'à'=>'a', 
			'á'=>'a', 
			'â'=>'a', 
			'ã'=>'a', 
			'ä'=>'a', 
			'å'=>'a', 
			'æ'=>'a', 
			'ç'=>'c', 
			'è'=>'e', 
			'é'=>'e',
			'ê'=>'e', 
			'ë'=>'e', 
			'ì'=>'i', 
			'í'=>'i', 
			'î'=>'i', 
			'ï'=>'i', 
			'ð'=>'o', 
			'ñ'=>'n', 
			'ò'=>'o', 
			'ó'=>'o',
			'ô'=>'o', 
			'õ'=>'o', 
			'ö'=>'o', 
			'ø'=>'o', 
			'ù'=>'u', 
			'ú'=>'u', 
			'û'=>'u', 
			'ý'=>'y', 
			'ý'=>'y', 
			'þ'=>'b',
			'ÿ'=>'y', 
			'Ã¡' => 'a',
			'Ã€' => 'A',
			'Ã¤' => 'a',
			'Ã©' => 'e',
			'Ã¨' => 'e',
			'Ã‰' => 'E',
			'Ãª' => 'e',
			'Ã¦' => 'A',
			'Ã*' => 'i',
			'Ã³' => 'o',
			'Ã“' => 'O',
			'Ã¶' => 'o',
			'Ãº' => 'u',
			'Ã¼' => 'u',
			'Ã±' => 'ñ',
			'Ã‘' => 'Ñ',
			'Ã§' => 'c'
		);
	   
		return strtr($string, $table);
	}
endif;
//Image functions
//You do not need to alter these functions
if ( !function_exists('resizeImage') ) :
function resizeImage($image,$width,$height,$scale) {
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	$source = imagecreatefromjpeg($image);
	imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
	imagejpeg($newImage,$image,90);
	chmod($image, 0777);
	return $image;
}
endif;
//You do not need to alter these functions
if ( !function_exists('resizeThumbnailImage') ) :
function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale){
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	$source = imagecreatefromjpeg($image);
	imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
	imagejpeg($newImage,$thumb_image_name,90);
	chmod($thumb_image_name, 0777);
	return $thumb_image_name;
}
endif;
//You do not need to alter these functions
if ( !function_exists('getHeight') ) :
function getHeight($image) {
	$sizes = getimagesize($image);
	$height = $sizes[1];
	return $height;
}
endif;
//You do not need to alter these functions
if ( !function_exists('getWidth') ) :
function getWidth($image) {
	$sizes = getimagesize($image);
	$width = $sizes[0];
	return $width;
}
endif;
if ( !function_exists('get_items') ) :
function get_items($cid){
	// Get the Cart on BD
	if ($cid)
		$cart = row_field_table ("clients", "client_id", "client_cart", $cid);
	else
		if (isset($_SESSION['cart']))
			$cart = $_SESSION['cart'];
	if (!empty($cart)){
		$cart = explode (",", $cart);
		$items = array();
		foreach ($cart as $v) {
			$item = explode("-", $v);
			$items[$item[0]] = $item[1];
		}
		return $items;
	}
	else
		return array();	
}
endif;
if ( !function_exists('splice_preserve_keys') ) :
function splice_preserve_keys(&$_arr, $_index, $_long){
           $_keys=array_keys($_arr);
           $_key=array_search($_index, $_keys);
           if ( $_key !== FALSE ){
               $_keys=array_splice($_keys, $_key, $_long);
              foreach ($_keys as $_key) unset($_arr[$_key]);
          }
}
endif;
if ( !function_exists('mxStates') ) :
function mxStates($option, $abbr) {
	$states = array(
		'AC'=>'Aguascalientes',
		'BN'=>'Baja California Norte',
		'BS'=>'Baja California Sur',
		'CA'=>'Campeche',
		'CS'=>'Chiapas',
		'CH'=>'Chihuahua',
		'CL'=>'Coahuila',
		'CO'=>'Colima',
		'DU'=>'Durango',
		'EM'=>'Edo. de M&eacute;xico',
		'GU'=>'Guanajuato',
		'GR'=>'Guerrero',
		'HG'=>'Hidalgo',
		'JA'=>'Jalisco',
		'DF'=>'M&eacute;xico DF',
		'MI'=>'Michoac&aacute;n',
		'MO'=>'Morelos',
		'NY'=>'Nayarit',
		'NL'=>'Nueva Le&oacute;n',
		'OX'=>'Oaxaca',
		'PU'=>'Puebla',
		'QT'=>'Queretaro',
		'QR'=>'Quintana Roo',
		'SP'=>'San Luis Potos&iacute;',
		'SL'=>'Sinaloa',
		'SN'=>'Sonora',
		'TA'=>'Tabasco',
		'TP'=>'Tamaulipas',
		'TX'=>'Tlaxcala',
		'VZ'=>'Veracruz',
		'YU'=>'Yucat&aacute;n',
		'ZC'=>'Zacatecas'
	);   
	switch ($option) {
		case 'name':
			return ($states[$abbr]);
			break;
		case 'dropdown':
			foreach($states as $k => $v) {
				$select = ($k == $abbr) ? " selected='selected'" : "";
				echo "<option value='$k'$select>".$states[$k]."</option>";
			}
			break;		
	}
}
endif;
?>