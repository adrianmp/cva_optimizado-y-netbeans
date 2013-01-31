<?php
	require("include/conexion.php");	
	$sql = "SELECT marca FROM new_products GROUP BY marca";
?>	
		<select id="brand">
		<option value="0">Elegir Una Marca</option>
<?php 
	
	$result = mysql_query($sql);	
	while ($row = mysql_fetch_row($result))
	{
		echo "<option value=".$row[0].">".$row[0]."</option>";
	}
?>
	</select>
