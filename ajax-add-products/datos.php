<meta name="viewport" content="width=device-width, initial-scale=1.0">	
<link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
<link type="text/css" rel="stylesheet" href="css/bootstrap-responsive.css"/> 
<?php 
					require("include/conexion.php");
					$brand = $_POST['brand'];
					$sql = "SELECT product_clave_cva FROM products WHERE product_clave_cva <> '' ORDER BY product_clave_cva ASC";
					$catalogo_cva_sql = mysql_query($sql, $conexion);
					while($row = mysql_fetch_array($catalogo_cva_sql)){
						$catalogo_cva[] = $row['product_clave_cva'];
					}
					if($brand)
						$sql = "SELECT product_name, product_clave_cva, grupo, marca, disponible, precio, moneda, product_id FROM new_products WHERE marca ='".$brand."'";
					else
						$sql = "SELECT product_name, product_clave_cva, grupo, marca, disponible, precio, moneda, product_id FROM new_products";
						
					$result = mysql_query($sql);
?>
					<table class="table table-hover">
						<thead>
							<tr>
								<th><b>Nombre Producto</b></th>
								<th><b>Grupo</b></th>
								<th><b>Marca</b></th>
								<th><b>Disponible</b></th>
								<th><b>Precio</b></th> 
								<th><b>Moneda</b></th>
								<th><b>Status</b></th>
							</tr>
						</thead>
					<tbody>
<?php
					while ($row = mysql_fetch_row($result))
					{
						echo "<tr align='justify'>";
						echo '<td><a id="v3" href="formulario.php?product_id='.$row[7].'">'.$row[0].'</a></td>';
						echo "<td>$row[2]</td>";
						echo "<td>$row[3]</td>";
						echo "<td>$row[4]</td>";
						echo "<td>$row[5]</td>";
						echo "<td>$row[6]</td>";
						//echo "<td>$row[1]</td>";
						if(in_array($row[1], $catalogo_cva , TRUE  ))
							echo '<td align="center"><img class="sku" src="img/stop.png" alt="Producto Repedito" width="12" height="12"></td>';
						else
?>
							<td>
								<a id="v3" href="formulario.php?product_id=<?php echo $row[7];?>">
									<img class="sku" src="img/accept.png" alt="Producto Repedito">
								</a>
							</td>								
<?php
						echo '</tr>';
					}					
					echo "</tbody>";					
					echo "</table>";
			?>	
