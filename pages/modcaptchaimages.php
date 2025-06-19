<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$images = array();
	$result = mysqli_query($link, "SELECT image_code, id, object_id FROM captcha_images WHERE approved = 0") or die(mysqli_error($link));
	if(mysqli_num_rows($result) == 0) return "There are no images pending approval / disapproval.";
	while($row = mysqli_fetch_assoc($result)) $images[$row['object_id']][] = array("code" => $row['image_code'], "id" => $row['id']);
?>
	<form action="index.php" method="POST" name="captcha_image_form">
	<input type="hidden" name="h" value="docaptchaimages">
<?php
	PrintMessage("Info", "Approve the image if it directly corresponds to the object it is describing. <br>Disapprove the image if it has no relation with the object it's describing or if the image is watermarked, of bad quality or has a name of a company on it.");
	foreach($images as $id => $image) {
	$name = mysqli_get_value("name", "captcha_objects", "id", $id);
	?>
		<div class="accordion">
			<input id="<?php echo $id; ?>" type="checkbox" class="accordion-toggle" checked>
			<label for="a1"><?php echo ucwords($name); ?></label>
			<div class="accordion-box">
			<div class="row light-bg">
			<?php
				foreach($image as $img) echo '<div style="float: left; padding: 10px; margin-right: 10px;"><img src="data:image/jpeg;base64,'.$img['code'].'" width="100" height="100"><br><input type="checkbox" name="id[]" value="'.$img['id'].'" id="img_'.$img['id'].'"><label for="img_'.$img['id'].'"></label></div>';
			?>
			</div>
			</div>
		</div>
<?php } ?>
	<input type="submit" name="action" value="Approve" onclick="return confirm('Are you sure you want to APPROVE these images?');">
	<input type="submit" name="action" value="Disapprove" class="bg-red" onclick="return confirm('Are you sure you want to disapprove these images?');">
	<input type="button" name="CheckAll" value="Check All" onclick="checkAll(document.captcha_image_form['id[]'],1)">
	<input type="button" name="UnCheckAll" value="Uncheck All" class="bg-red" onclick="checkAll(document.captcha_image_form['id[]'],0)">
	</form>


