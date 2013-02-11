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
	function download_page($path){
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
	
	//Variables
	$disponible = 0;
	$brand_id = 47;
	$result_new = 0;
	////////////////Consulta en products///////////////////////////////
	$sql = "SELECT product_clave_cva FROM products WHERE product_clave_cva <> '' AND product_brand = 74 ORDER BY product_clave_cva ASC";
	$catalogo_cva_sql = mysql_query($sql);
	$to = mysql_num_rows(mysql_query($sql));
	if($to!=0)
	{
		while($row = mysql_fetch_array($catalogo_cva_sql)){
			$catalogo_cva[] = $row['product_clave_cva'];
		}
		$to = 1;
	} 
	/////////////////////////consulta en new products/////////////////////////////
	$sql_new = "SELECT product_clave_cva FROM new_products WHERE product_clave_cva <> '' AND brand_id= 74 ORDER BY product_clave_cva ASC";
	$catalogonew = mysql_query($sql_new);
	$to1 = mysql_num_rows(mysql_query($sql_new));
	if($to1!=0)
	{
		while($rownew = mysql_fetch_array($catalogonew)){
			$catalogo_new[] = $rownew[0];
		}
		$to1 = 1;
	}
	/////////////////////////////////////////////////////7
	$sql = "SELECT MAX(product_id) as maximo FROM  new_products ORDER BY  new_products.product_id ASC";
	$product_id = mysql_query($sql);
	if($row = mysql_fetch_array($product_id))
		$id = $row['maximo'];	
	else
		$id = 0;
	//////////////////////////////////Grupo cva XML/////////////////////7
	$sXML = download_page('http://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=26683&marca=TRANSCEND&grupo%&clave=%&codigo=%&sucursales=1&tc=1&tipo=1&depto=1&dt=1&dc=1&promos=1');
	$product_n=0;
	
	if($sXML)
	{
		$catalogo = new SimpleXMLElement($sXML);
		foreach( $catalogo->item as $item ) 
		{
			if($item->clave)
			{
				switch ($to) 
				{
					case 0:
						switch ($to1) 
						{
							////////////////No esta en ninguna de las dos tablas /////////////////
							case 0:
									$disponible = $item->disponible + $item->disponibleCD + $item->VENTAS_CANCUN + $item->VENTAS_CULIACAN + $item->VENTAS_HERMOSILLO + $item->VENTAS_LEON + $item->VENTAS_MERIDA + $item->VENTAS_MEXICO + $item->VENTAS_MONTERREY + $item->VENTAS_PLAZA_DE_LA_COMPUTACION_GDL + $item->VENTAS_PUEBLA + $item->VENTAS_QUERETARO + $item->VENTAS_TEPIC + $item->VENTAS_TORREON + $item->VENTAS_TUXTLA + $item->VENTAS_VERACRUZ + $item->VENTAS_VILLAHERMOSA;
									if($disponible >0)
									{
										$id+=1;
										if( $item->VencimientoPromocion != "Sin Descuento" )
										{
											$pieces = explode("/", $item->VencimientoPromocion);
											$VencimientoPromocion = $pieces[1]."/".$pieces[0]."/".$pieces[2];
										}
										else { $VencimientoPromocion = $item->VencimientoPromocion; }
										
										$sql = "insert new_products values (".$id.", ".$brand_id.", '".$item->clave."', '".$item->codigo_fabricante."', '".$item->descripcion."', '".$item->ficha_tecnica."', '".$item->ficha_comercial."', '".$item->grupo."', '".$item->marca."', ".$disponible.", ".$item->precio.", '".$item->moneda."', ".$item->tipocambio.", '".$item->fechaactualizatipoc."', '".$item->TotalDescuento."', '".$item->PrecioDescuento."', '".$item->DescripcionPromocion."', '".$VencimientoPromocion."', '".$item->TipoProducto."', '".$item->Departamento."', '".$item->imagen."')";
										$result_new = mysql_query($sql);
									}
								break;
							case 1:
								////////////////Esta solo en la tabla de new products /////////////////
									if(!in_array($item->clave, $catalogo_new))
									{
										$disponible = $item->disponible + $item->disponibleCD + $item->VENTAS_CANCUN + $item->VENTAS_CULIACAN + $item->VENTAS_HERMOSILLO + $item->VENTAS_LEON + $item->VENTAS_MERIDA + $item->VENTAS_MEXICO + $item->VENTAS_MONTERREY + $item->VENTAS_PLAZA_DE_LA_COMPUTACION_GDL + $item->VENTAS_PUEBLA + $item->VENTAS_QUERETARO + $item->VENTAS_TEPIC + $item->VENTAS_TORREON + $item->VENTAS_TUXTLA + $item->VENTAS_VERACRUZ + $item->VENTAS_VILLAHERMOSA;
											if($disponible >0)
											{
												$id+=1;
												if( $item->VencimientoPromocion != "Sin Descuento" )
												{
													$pieces = explode("/", $item->VencimientoPromocion);
													$VencimientoPromocion = $pieces[1]."/".$pieces[0]."/".$pieces[2];
												}
												else { $VencimientoPromocion = $item->VencimientoPromocion; }
												
												$sql = "insert new_products values (".$id.", ".$brand_id.", '".$item->clave."', '".$item->codigo_fabricante."', '".$item->descripcion."', '".$item->ficha_tecnica."', '".$item->ficha_comercial."', '".$item->grupo."', '".$item->marca."', ".$disponible.", ".$item->precio.", '".$item->moneda."', ".$item->tipocambio.", '".$item->fechaactualizatipoc."', '".$item->TotalDescuento."', '".$item->PrecioDescuento."', '".$item->DescripcionPromocion."', '".$VencimientoPromocion."', '".$item->TipoProducto."', '".$item->Departamento."', '".$item->imagen."')";
												$result_new = mysql_query($sql);
											}
									}
								break;
						}
							
								
						break;
					case 1:
						////////////////Esta solo en la tabla de products /////////////////
						switch ($to1) 
						{
							case 0:
								if(!in_array($item->clave, $catalogo_cva))
								{
										$disponible = $item->disponible + $item->disponibleCD + $item->VENTAS_CANCUN + $item->VENTAS_CULIACAN + $item->VENTAS_HERMOSILLO + $item->VENTAS_LEON + $item->VENTAS_MERIDA + $item->VENTAS_MEXICO + $item->VENTAS_MONTERREY + $item->VENTAS_PLAZA_DE_LA_COMPUTACION_GDL + $item->VENTAS_PUEBLA + $item->VENTAS_QUERETARO + $item->VENTAS_TEPIC + $item->VENTAS_TORREON + $item->VENTAS_TUXTLA + $item->VENTAS_VERACRUZ + $item->VENTAS_VILLAHERMOSA;
										if($disponible >0)
										{
											$id+=1;
											if( $item->VencimientoPromocion != "Sin Descuento" )
											{
												$pieces = explode("/", $item->VencimientoPromocion);
												$VencimientoPromocion = $pieces[1]."/".$pieces[0]."/".$pieces[2];
											}
											else { $VencimientoPromocion = $item->VencimientoPromocion; }
											
											$sql = "insert new_products values (".$id.", ".$brand_id.", '".$item->clave."', '".$item->codigo_fabricante."', '".$item->descripcion."', '".$item->ficha_tecnica."', '".$item->ficha_comercial."', '".$item->grupo."', '".$item->marca."', ".$disponible.", ".$item->precio.", '".$item->moneda."', ".$item->tipocambio.", '".$item->fechaactualizatipoc."', '".$item->TotalDescuento."', '".$item->PrecioDescuento."', '".$item->DescripcionPromocion."', '".$VencimientoPromocion."', '".$item->TipoProducto."', '".$item->Departamento."', '".$item->imagen."')";
											$result_new = mysql_query($sql);
										}
								}
										
								break;
							case 1:
								////////////////Esta  en las 2 tablas/////////////////
								if(!in_array($item->clave, $catalogo_cva))
								{
										if(!in_array($item->clave, $catalogo_new))
										{
											$disponible = $item->disponible + $item->disponibleCD + $item->VENTAS_CANCUN + $item->VENTAS_CULIACAN + $item->VENTAS_HERMOSILLO + $item->VENTAS_LEON + $item->VENTAS_MERIDA + $item->VENTAS_MEXICO + $item->VENTAS_MONTERREY + $item->VENTAS_PLAZA_DE_LA_COMPUTACION_GDL + $item->VENTAS_PUEBLA + $item->VENTAS_QUERETARO + $item->VENTAS_TEPIC + $item->VENTAS_TORREON + $item->VENTAS_TUXTLA + $item->VENTAS_VERACRUZ + $item->VENTAS_VILLAHERMOSA;
												if($disponible >0)
												{
													$id+=1;
													if( $item->VencimientoPromocion != "Sin Descuento" )
													{
														$pieces = explode("/", $item->VencimientoPromocion);
														$VencimientoPromocion = $pieces[1]."/".$pieces[0]."/".$pieces[2];
													}
													else { $VencimientoPromocion = $item->VencimientoPromocion; }
													
													$sql = "insert new_products values (".$id.", ".$brand_id.", '".$item->clave."', '".$item->codigo_fabricante."', '".$item->descripcion."', '".$item->ficha_tecnica."', '".$item->ficha_comercial."', '".$item->grupo."', '".$item->marca."', ".$disponible.", ".$item->precio.", '".$item->moneda."', ".$item->tipocambio.", '".$item->fechaactualizatipoc."', '".$item->TotalDescuento."', '".$item->PrecioDescuento."', '".$item->DescripcionPromocion."', '".$VencimientoPromocion."', '".$item->TipoProducto."', '".$item->Departamento."', '".$item->imagen."')";
													$result_new = mysql_query($sql);
												}
										}
								}
								break;
						}
						break;
					
				}
			}
			
		}
	}
		if($result_new)
			echo '<h4>La actualizacion Finalizo Cierra esta Ventana</h4>';
		else
			echo "<h4>Esta Marca ya Esta Actualizada Cierra esta Ventana</h4>";
