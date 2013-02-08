<?php

	$is_admin_client_page = TRUE;
	include ('include/init.php');
	
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
	$disponible = 0;
	$brand_id = 122;
	$result_new = 0;
	$sql = "SELECT product_clave_cva FROM products WHERE product_clave_cva <> '' AND product_brand = 122 ORDER BY product_clave_cva ASC";
	$catalogo_cva_sql = mysql_query($sql);
	while($row = mysql_fetch_array($catalogo_cva_sql)){
		$catalogo_cva[] = $row['product_clave_cva'];
	}
	
	$sql_new = "SELECT product_clave_cva FROM new_products WHERE product_clave_cva <> '' AND brand_id= 122 ORDER BY product_clave_cva ASC";
	$catalogonew = mysql_query($sql_new);
	while($rownew = mysql_fetch_array($catalogonew)){
		$catalogo_new[] = $rownew[0];
	}
	
	$sql = "SELECT MAX(product_id) as maximo FROM  new_products ORDER BY  new_products.product_id ASC";
	$product_id = mysql_query($sql);
	if($row = mysql_fetch_array($product_id))
		$id = $row['maximo'];	
	else
		$id = 0;
	
	$sXML = download_page('http://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=26683&marca=INGRESSIO&grupo%&clave=%&codigo=%&sucursales=1&tc=1&tipo=1&depto=1&dt=1&dc=1&promos=1');
	$product_n=0;
	if($sXML){
		$catalogo = new SimpleXMLElement($sXML);
		foreach( $catalogo->item as $item ) {
			if($item->clave){
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
				
			}
			
		}
	}
		if($result_new)
			echo '<h4>La actualizacion Finalizo Cierra esta Ventana</h4>';
		else
			echo "<h4>Esta Marca ya Esta Actualizada Cierra esta Ventana</h4>";
?>
