<?php
	session_start();
	$is_admin_client_page = TRUE;
	if (file_exists('../include/bdconnect.php'))
		include '../include/bdconnect.php';
	else
		header('Location: maintenance.html');
	include '../include/functions.php';

	$logged_in = $client_logged_in = FALSE;
	// if session is not set redirect the user to logout
	if(isset($_SESSION['uid']) && !empty($_SESSION['uid']) && $_SESSION['uid']) {
		$logged_in = TRUE;
		$uid = $_SESSION['uid'];
		$user = user($uid);
		$_SESSION['cid'] = $cid = 0;
		if (!$user['user_status'])
			header('Location: logout');
	} 
	
	if(isset($_SESSION['cid']) && !empty($_SESSION['cid']) && $_SESSION['cid']) {
		$client_logged_in = TRUE;
		$cid = $_SESSION['cid'];
		$client = client($cid);
		if (!$client['client_status'])
			header('Location: logout');
	}
	if(!isset($_SESSION['cid']) && $is_admin_client_page)
		header("Location: /?error");
	include '../include/es.php';
?>
<?php
	class Update 
	{
		
		//declaracion de atributos privados
		private $sXML = "",$catalogo_new  = array();
		private $catalogo_cva  = array(), $product_category = array();
		
		function __construct() 
		{
	   		$this->sXML = $this->download_page('http://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=26683&marca=ASPEL&grupo%&clave=%&codigo=%&sucursales=1&tc=1&tipo=1&depto=1&dt=1&dc=1&promos=1');
		}
		
		function google($amount, $conversion)
		{
			/*$from = "USD";
			$to = "MXN";
			$string = "1".$from."=?".$to;
			$google_url = "http://www.google.com/ig/calculator?hl=en&q=".$string;
			$result = file_get_contents($google_url);
			$result = explode('"', $result);
			$converted_amount = explode(' ', $result[3]);
			$conversion = (float) $converted_amount[0];*/
			// $conversion = preg_replace('/[x00-x08x0B-x1F]/', '', $conversion);
			$con = (float) $conversion;
			echo $amount."    ".$con."    ";
			$con = $con * $amount;
			$con = round($con, 2);
			//echo ($converted_amount[0]+.4)."<br/>";
			//Get text for converted currency
			// $rhs_text = ucwords(str_replace($converted_amount[0],"",$result[3]));
			//Make right hand side string
			$rhs = $con;
			echo $rhs."<br/>";
			return $rhs;
		}

		function load_price($id, $price)
		{
			$cid = (int) $id;
			$c = row_field_table ("categories", "category_id", "", $cid);
			if (empty($c['category_pt']))
				exit;
			 // Product Price
			return load_price_from_pt ($c['category_pt'], $price);
		}

		function download_page($path)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$path);
			curl_setopt($ch, CURLOPT_FAILONERROR,1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			$retValue = curl_exec($ch);	
			curl_close($ch);
			return $retValue;
		}

		function Clave_Unica($cva)
		{
			$sql = "SELECT `product_sku`, `product_clave_ctin` FROM `products` WHERE `product_clave_cva`= '".$cva."' LIMIT 1";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			if(( $row[0] !="" )||( $row[1] !=""))
				return TRUE;
			else
				return FALSE;
		}

		
		function products()
		{
			$sql = "SELECT product_clave_cva, product_category FROM products WHERE product_clave_cva <> '' AND product_brand = 109 ORDER BY product_clave_cva ASC";
			$catalogo_cva_sql = mysql_query($sql);
			$to = mysql_num_rows(mysql_query($sql));
			if($to!=0)
			{
				while($row = mysql_fetch_array($catalogo_cva_sql)){
					$this->catalogo_cva[] = $row[0];
					$this->product_category[] = $row[1];
				}
				$to = 1;
			} 
			return $to;
		}
		
		function eliminados()
		{
			$query="";
			$size = sizeof($this->catalogo_cva);
			echo count($this->catalogo_cva);
			for ($i=0; $i < $size; $i++) 
			{
				if(!in_array($this->catalogo_cva[$i], $this->catalogo_new))
				{
					$temp = '';
					$query = '';
					$temp = $this->Clave_Unica($this->catalogo_cva[$i]);
					if($temp==TRUE)
					{
						$query= "UPDATE products SET product_clave_cva = ' ', product_next_update ='".date("Y-m-d")." 19:00:00', product_status = 0  WHERE product_clave_cva='".$this->catalogo_cva[$i]."'";
						mysql_query($query);
					}
					else {
						$query= "UPDATE products SET  product_next_update ='2020-12-31 19:00:00', product_status = 0  WHERE product_clave_cva='".$this->catalogo_cva[$i]."'";
						mysql_query($query);
					}
				}
			}
					
		}
		
		public function XML()
		{
			$num = $this->products();
			$pos = 0;
			if($this->sXML)
			{
				$catalogo = new SimpleXMLElement($this->sXML);
				foreach($catalogo->item as $item ) 
				{
					$this->catalogo_new[] = $item->clave;
					if ($num) 
					{
						if(in_array($item->clave, $this->catalogo_cva))
						{
							
							$disponible = $item->disponible + $item->disponibleCD 
							+ $item->VENTAS_CANCUN + 
							$item->VENTAS_CULIACAN + 
							$item->VENTAS_HERMOSILLO + 
							$item->VENTAS_LEON + $item->VENTAS_MERIDA + 
							$item->VENTAS_MEXICO + $item->VENTAS_MONTERREY + 
							$item->VENTAS_PLAZA_DE_LA_COMPUTACION_GDL + 
							$item->VENTAS_PUEBLA + $item->VENTAS_QUERETARO + 
							$item->VENTAS_TEPIC + $item->VENTAS_TORREON + 
							$item->VENTAS_TUXTLA + $item->VENTAS_VERACRUZ + 
							$item->VENTAS_VILLAHERMOSA;													
							$sql = "";
							if($item->moneda =="Dolares")
							{
								$dolar =(float) $item->precio;
								$p = (float) $this->google($dolar, $item->tipocambio);
								$product_price_mx = $this->load_price($this->product_category[$pos], $p);	
								$sql = "UPDATE products SET product_buy_mx = " . $p  . " , product_status = 1, product_price_mx =".$product_price_mx." , product_next_update ='0000-00-00 17:00:00'";
								$dolares = TRUE;
							}
							else 
							{
								$product_price_mx  = $this->load_price($this->product_category[$pos], $item->precio);
								$sql = "UPDATE products SET product_buy_mx = " . $item->precio  . " , product_status = 1,  product_price_mx =".$product_price_mx." , product_next_update ='0000-00-00 17:00:00'";
								$dolares = FALSE;
							}
							if($item->PrecioDescuento!="Sin Descuento")
							{
								
								if($dolares == TRUE)
								{
									$product_buy_mx_offer = $this->load_price($this->product_category[$pos], $this->google($item->PrecioDescuento, $item->tipocambio));
									$pieces = explode("/", $item->VencimientoPromocion);
									$VencimientoPromocion = $pieces[2]."-".$pieces[1]."-".$pieces[0];
									$sql = "UPDATE products SET product_buy_mx = " . $p  . " , product_status = 1,  product_buy_mx_offer =".$this->google($item->PrecioDescuento, $item->tipocambio)."\n"
									." , product_price_mx =".$product_buy_mx_offer." , product_featured_end ='".$VencimientoPromocion."19:00:00' , product_next_update ='0000-00-00 17:00:00'";
								}
								else
								{
									$product_buy_mx_offer = $this->load_price($this->product_category[$pos], $item->PrecioDescuento);
									$pieces = explode("/", $item->VencimientoPromocion);
									$VencimientoPromocion = $pieces[2]."-".$pieces[1]."-".$pieces[0];
									$sql = "UPDATE products SET product_buy_mx = " . $item->precio  . " ,  product_status = 1,  product_buy_mx_offer =".$item->PrecioDescuento."\n,".
									"  product_price_mx =".$product_buy_mx_offer." , product_featured_end ='".$VencimientoPromocion."19:00:00' , product_next_update ='0000-00-00 17:00:00'";
								}
							}
							if($disponible==0)
							{
								$sql = str_replace("product_status = 1", "product_status = 0", $sql);
								$sql .= ", product_next_update ='".date("Y-m-d")." 17:00:00'";
							}
							$sql.=" WHERE product_clave_cva = '" . $item->clave . "'"; 																										
							$result_new = mysql_query($sql);									
						}
					}
					
				}	
			}
		}
	}
	
	$update = new Update();
	$update->XML();
	$update->eliminados();
?>
			
