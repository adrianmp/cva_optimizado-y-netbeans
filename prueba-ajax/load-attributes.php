<?php
include 'conexion.php';
$category = (int) filter_var( $_GET['category'], FILTER_SANITIZE_NUMBER_INT );
//$attributes = $_GET['attributes'];
$attributes = "";
$result = mysql_query("SELECT * FROM attributes WHERE att_status = 1 AND att_category = ". $category ." ORDER BY att_order ASC");
$natts = mysql_num_rows($result);
if ($natts > 1):
	// echo '<div id="attributes">';
	$apa = array_unique( explode( ",", $attributes ) );
	while ($att = mysql_fetch_array($result)):
	if (!$att['att_parent']):
		echo "<strong>" . strtoupper(stripslashes($att['att_value'])) . "</strong>";
		continue;
	else:
	?>
		<div class="pa">
			<label><?php echo stripslashes($att['att_value']);?></label>
			<input type="checkbox" name="attributes[]" <?php if(in_array($att['att_id'], $apa)) echo 'checked="checked"';?> class="required" size="5" value="<?php echo $att['att_id'];?>" />
		</div>
		<div class="clear"></div>
	<?php
	endif;
  endwhile;
  // echo '</div>';
else:
	echo '<label>Sin atributos</label><div class="clear"></div>';
endif;
?>