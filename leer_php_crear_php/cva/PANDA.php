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
	//include 'conexion.php';
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
	class Update 
	{
		
		//declaracion de atributos privados
		private $sXML = "",$catalogo_new  = array();
		private $catalogo_cva  = array(), $product_category = array(), $product_next_update = array(), $product_status= array(),$product_stock = array();
		
		function __construct() 
		{
	   		$this->sXML = $this->download_page('http://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=26683&marca=PANDA&grupo%&clave=%&codigo=%&sucursales=1&tc=1&tipo=1&depto=1&dt=1&dc=1&promos=1');
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
			$con = $con * $amount;
			$con = round($con, 2);
			//echo ($converted_amount[0]+.4)."<br/>";
			//Get text for converted currency
			// $rhs_text = ucwords(str_replace($converted_amount[0],"",$result[3]));
			//Make right hand side string
			$rhs = $con;
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
			$sql = "SELECT product_clave_cva, product_category, product_next_update, product_status, product_stock FROM products WHERE product_clave_cva <> '' AND product_brand = 37 ORDER BY product_clave_cva ASC";
			$catalogo_cva_sql = mysql_query($sql);
			$to = mysql_num_rows(mysql_query($sql));
			if($to!=0)
			{
				while($row = mysql_fetch_array($catalogo_cva_sql)){
					$this->catalogo_cva[] = $row[0];
					$this->product_category[] = $row[1];
					$this->product_next_update[] = $row[2];
					$this->product_status[] = $row[3];
					$this->product_stock[] = $row[4];
				}
				$to = 1;
			} 
			return $to;
		}
		
		function status($cva)
		{
			$sql = "SELECT product_status FROM products WHERE `product_clave_cva`= '".$cva."' LIMIT 1";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			return $row[0];
		}
		
		function eliminados()
		{
			$query="";
			$permiso = FALSE;
			$size = sizeof($this->catalogo_cva);
			//echo count($this->catalogo_cva);
			for ($i=0; $i < $size; $i++) 
			{
				if(!in_array($this->catalogo_cva[$i], $this->catalogo_new))
				{
					$query = '';
					$temp = '';
					$true = strstr($this->catalogo_cva[$i], ',', TRUE);
					$tfecha = explode("-", $this->product_next_update[$i]);
					$temp = $this->Clave_Unica($this->catalogo_cva[$i]);
					if($true)
					{
						$clave = explode(",", str_replace(" ","", trim($this->catalogo_cva[$i])) );
						if( ($this->status($clave[0])) || ($this->status($clave[1])) )
						{
							$query= "UPDATE products SET  product_next_update ='".date("Y-m-d")." 23:23:23', product_status = 0  WHERE product_clave_cva='".$this->catalogo_cva[$i]."'";	
							//echo $query."<br/>";
							mysql_query($query);
							$permiso = TRUE; 	
						}
						else
							$permiso = FALSE;	
					}
					else
						$permiso = FALSE;
					
					if($temp==TRUE)
					{
						if($permiso == FALSE)
						{
							if( ($tfecha[0] < 2020) && ($this->product_status[$i] == 1))
							{
								$query= "UPDATE products SET  product_next_update ='".date("Y-m-d")." 23:23:23', product_status = 0  WHERE product_clave_cva='".$this->catalogo_cva[$i]."'";	
								//echo $query."<br/>"; 		
								mysql_query($query);
							}
						}
					}
					else {
						if($permiso == FALSE)
						{
							if($this->product_stock[$i] > 0)
							{
								$query= "UPDATE products SET  product_next_update ='".date("Y-m-d")." 23:23:23', product_status = 0  WHERE product_clave_cva='".$this->catalogo_cva[$i]."'";	
								//echo $query."<br/>"; 		
								mysql_query($query);
							}
							else
							{
								$query= "UPDATE products SET  product_next_update ='2020-12-31 01:00:00', product_status = 0  WHERE product_clave_cva='".$this->catalogo_cva[$i]."'";
								//echo $query."<br/>"; 	
								mysql_query($query);
							}	
						}
					}
				}
			}
			echo "<h1>La actualización Termino!!!!!</h1>";
					
		}
		
		function getMonthDays($Month, $Year)
		{
		   //Si la extensión que mencioné está instalada, usamos esa.
		   if( is_callable("cal_days_in_month"))
		   {
		      return cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
		   }
		   else
		   {
		      //Lo hacemos a mi manera.
		      return date("d",mktime(0,0,0,$Month+1,0,$Year));
		   }
		}
		
		function day($years, $months, $days)
		{
			//fecha actual hoy
			date_default_timezone_set("America/Mexico_City");
			$hoy = explode ("-", date('Y-m-d'));
			$year = $hoy[0];
			$month = $hoy[1];
			$day = $hoy[2];	
			//Calculo diferencias
			$y = $years - $year;
			$d = $days - $day;
			$m = $months - $month;
			$dm = $this->getMonthDays($month, $year);
			$total = $dm - $day;
			//comparaciones
			if($y == 0)
			{
				if($m == 0)
					return ($d>=0 && $d<6) ? '0' : '1';
				else 
				{
					if($m>0)
					{
						if($days >= 1 && $days < 6)
							return ($total > 5 && $total<31) ? "1" : "0" ;
						else 
							return '1';
					}
				}
			}
			else 
			{
				if($m == 0)
					return ($d>=0 && $d<6) ? '0' : '1'; 
				else 
				{
					if($m>0)
					{
						if($days >= 1 && $days < 6)
							return ($total > 5 && $total<31) ? "1" : "0" ;
						else 
							return '1';
					}
					else 
					{
						if($days >= 1 && $days < 6)
							return ($total > 5 && $total<31) ? "1" : "0" ;
						else 
							return '1';
					}
				}
			}
						
		}
		
		public function XML()
		{
			$num = $this->products();
			$p = 0;
			if($this->sXML)
			{
				$catalogo = new SimpleXMLElement($this->sXML);
				foreach($catalogo->item as $item ) 
				{
					$this->catalogo_new[] = $item->clave;
					if ($num) 
					{
						if(in_array($item->clave, $this->catalogo_cva, FALSE))
						{
							$clave = array_search($item->clave, $this->catalogo_cva, FALSE);
							//echo $clave."<br/>";
							
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
							//echo $item->clave."  ".$this->product_category[$clave]."     ".$item->grupo."<br/>";
							if($item->moneda =="Dolares")
							{
								$dolar =(float) $item->precio;
								$p = (float) $this->google($dolar, $item->tipocambio);
								$product_price_mx = $this->load_price($this->product_category[$clave], $p);	
								$sql = "UPDATE products SET product_buy_mx = " . $p  . " , product_status = 1, product_featured = 0, product_featured_end ='0000-00-00 00:00:00', product_price_mx =".$product_price_mx.",\n".
								" product_buy_mx_offer = 0.00 , product_next_update ='".date("Y-m-d")." 19:00:00' + INTERVAL 5 DAY, product_updated = NOW()";
								$dolares = TRUE;
							}
							else 
							{
								$product_price_mx  = $this->load_price($this->product_category[$clave], $item->precio);
								$sql = "UPDATE products SET product_buy_mx = " . $item->precio  . " , product_status = 1, product_featured = 0, product_featured_end ='0000-00-00 00:00:00',\n".
								" product_buy_mx_offer = 0.00 ,  product_price_mx =".$product_price_mx." , product_next_update ='".date("Y-m-d")." 19:00:00' + INTERVAL 5 DAY, product_updated = NOW()";
								$dolares = FALSE;
							}
							if($item->PrecioDescuento!="Sin Descuento")
							{
								$pieces = explode("/", $item->VencimientoPromocion);
								$VencimientoPromocion = $pieces[2]."-".$pieces[1]."-".$pieces[0];
								$date =$this-> day($pieces[2], $pieces[1], $pieces[0]);
								if($dolares == TRUE)
								{
									$p1 = $this->google($item->PrecioDescuento, $item->tipocambio);
									$product_buy_mx_offer = $this->load_price($this->product_category[$clave], $p1);
									$sql = "UPDATE products SET product_buy_mx = " . $p  . " , product_status = 1,  product_buy_mx_offer =".$p1."\n"
									." , product_price_mx =".$product_buy_mx_offer." , product_featured_end ='".$VencimientoPromocion." 09:00:00' ,\n".
									" product_featured = 1, product_updated = NOW(), product_next_update = ";
									if($date)
										$sql .= "'".$VencimientoPromocion." 09:00:00' - INTERVAL 5 DAY ";
									else
										$sql .= "'".$VencimientoPromocion." 09:00:00'";
									
								}
								else
								{
									$product_buy_mx_offer = $this->load_price($this->product_category[$clave], $item->PrecioDescuento);
									$sql = "UPDATE products SET product_buy_mx = " . $item->precio  . " ,  product_status = 1,  product_buy_mx_offer =".$item->PrecioDescuento."\n,".
									"  product_price_mx =".$product_buy_mx_offer." , product_featured_end ='".$VencimientoPromocion." 09:00:00' ,\n".
									" product_featured = 1, product_updated = NOW(), product_next_update =";
									if($date)
										$sql .= "'".$VencimientoPromocion." 09:00:00' - INTERVAL 5 DAY ";
									else
										$sql .= "'".$VencimientoPromocion." 09:00:00'";
								}
							}
							if($disponible==0)
							{
								$sql = str_replace("product_status = 1", "product_status = 0", $sql);
								$sql .= ", product_next_update ='".date("Y-m-d")." 23:23:23'";
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
			
