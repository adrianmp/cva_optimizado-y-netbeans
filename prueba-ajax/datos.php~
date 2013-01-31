<?php 
					require("conexion.php");
					$brand = $_POST["brand"];
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

					echo "<table id='mytable'>";
					echo "<thead>";
					echo "<tr>";
					echo "<th><b>Nombre Producto</b></th>";
					echo "<th><b>Grupo</b></th>";
					echo "<th><b>Marca</b></th>";
					echo "<th><b>Disponible</b></th>";
					echo "<th><b>Precio</b></th> ";
					echo "<th><b>Moneda</b></th>";
					echo "<th><b>Status</b></th>";
					echo "</tr>";
					echo "</thead>";
					echo "<tbody>";
					while ($row = mysql_fetch_row($result))
					{
						echo "<tr align='justify'>";
						echo "<td>$row[0]</td>";
						echo "<td>$row[2]</td>";
						echo "<td>$row[3]</td>";
						echo "<td align='center'>$row[4]</td>";
						echo "<td align='center' >$row[5]</td>";
						echo "<td align='center' >$row[6]</td>";
						//echo "<td>$row[1]</td>";
						if(in_array($row[1], $catalogo_cva , TRUE  ))
							echo '<td align="center"><img class="sku" src="stop.png" alt="Producto Repedito" width="12" height="12"></td>';
						else
							echo '<td align="center"><a id="v3" href="formulario.php?product_id='.$row[7].'"><img class="sku" src="accept.png" alt="Producto Repedito"></a></td>';								
						echo '</tr>';
					}					
					echo "</tbody>";					
					echo "</table>";					
			?>	
