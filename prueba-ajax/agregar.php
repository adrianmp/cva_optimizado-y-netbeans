<?php
	require("conexion.php");
	require("functions.php");
	require_once("class.inputfilter.php");
	$ifilter = new InputFilter();
	$_POST = $ifilter->process($_POST);
	$upload_dir = "/var/www/bejattos/product"; 				// The directory for the images to be saved in
	$upload_path = $upload_dir."/";				// The path to where the image will be saved
	$max_file = 1048576;					// Approx 2MB
	if (isset($_POST["submit"])) {
		$errors = array();
		//Only process if the file is a JPG and below the allowed limit
		if((!empty($_FILES["file"])) && ($_FILES['file']['error'] == 0)){
			//Get the file information
			$userfile_name = $_FILES['file']['name'];
			echo $userfile_name;
			$userfile_tmp = $_FILES['file']['tmp_name'];
			$userfile_size = $_FILES['file']['size'];
			$filename = basename($_FILES['file']['name']);
			$file_ext = substr($filename, strrpos($filename, '.') + 1);
			if (($file_ext!="jpg") && ($userfile_size > $max_file)) {
				$errors[] = "Solo se aceptan images en formato jpeg y menores a 1MB.";
			}
		}else{
			$errors[] = "Selecciona una imagen JPEG para subirla.";
		}
		//Everything is ok, so we can upload the image.
		if (empty($errors)){
			
			if (isset($_FILES['file'])) {
				$userfile_name = $_FILES['file']['name'];
				$userfile_tmp = $_FILES['file']['tmp_name'];
				$userfile_size = $_FILES['file']['size'];
				$filename = basename($_FILES['file']['name']);
				$file_ext = substr($filename, strrpos($filename, '.') + 1);			
				
				// Values received			
				$utime = $_POST['real_file_name'];			
				$name = addslashes($_POST['name']);	
				$pn = trim($_POST['pn']);
				$stock = (int) $_POST['stock'];
				$sku = trim($_POST['sku']);
				$cva = trim($_POST['cva']);
				$ctin = trim($_POST['ctin']);			
				$cat = (int) $_POST['category'];
				$brand = (int) $_POST['brand'];
				$featured = isset($_POST['featured']) ? 1 : 0;
				$featured_end = ($featured) ? datepicker_to_mysql ($_POST['featured_end'], "18:00:00") : today_mysql_datetime();
				$desc = addslashes($_POST['desc']);
				$price_mx = (!isset($_POST['price_mx'])) ? 0 : str_replace(",", "", $_POST['price_mx']);
				$ext = get_file_extension($_FILES['file']['name']);
				$file_location = $upload_path . $utime . '.' . $ext;
		
				$was_uploaded = move_uploaded_file($userfile_tmp, $file_location);
				// chmod($file_location, 0777);
				
				// Show Errors List
				if(!empty($errors)) {
					echo array_errors ($errors);
					// exit;
				}
				else {
					// Insert File into BD
					$sql = "INSERT INTO products (product_sku, product_name, product_slug, product_desc, product_brand, product_buy_mx, product_buy_mx_offer, product_price_mx, product_category, product_image_uri, product_uploaded, product_status, product_featured, product_featured_end, product_clave_cva, product_clave_ctin, product_pn, product_stock, product_info_uri, product_related, product_next_update, product_attributes, product_user_upl)
							VALUES ('" . $sku ."', '" . $name ."', '" . $_POST['slug'] ."', '" . $desc ."', '" . $brand ."', '" . str_replace(",", "", $_POST['price_buy_mx']) ."', '".str_replace(",", "", trim($_POST['price_buy_mx_offer']))."', $price_mx , $cat, '/" . $file_location . "', '" . today_mysql_datetime() . "', '". (isset($_POST['product_status']) ? 1 : 0) ."', $featured, '".$featured_end."', '" . $cva ."', '" . $ctin ."', '" . $pn ."', '" . $stock ."', '" . urlencode($_POST['info_uri']) ."', '". ((isset($_POST['related_products'])) ? implode(",", $_POST['related_products']) : "") ."', '". datepicker_to_mysql ($_POST['next_update'], "08:00:00") ."', '" . ((isset($_POST['attributes'])) ? implode(",", $_POST['attributes']) : "") . "', 6)";
					$query = mysql_query($sql);
					$id = mysql_insert_id();
					echo $query;
					// echo mysql_error();		
				}
			}
		}
	}
?>
