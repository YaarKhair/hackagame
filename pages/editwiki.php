<?php
	// permissiongroups
	include("modules/permissions.php");

	$wiki_title = "";
	$wiki_text = "Enter your wiki article here";

	if (!empty($_REQUEST['title'])) $wiki_title = sql($_REQUEST['title']);
	
	$result = mysqli_query($link, "SELECT * FROM wiki WHERE title = '$wiki_title' AND pending = 0");
	if (mysqli_num_rows($result) == 0) {
		$wiki_id = 0;
		$wiki_cat1_id = 0;
		$wiki_cat2_id = 0;
		$wiki_text = "";
	}
	else {
		$row = mysqli_fetch_assoc ($result);
		$wiki_id = $row['id'];
		$wiki_cat1_id = $row['cat1_id'];
		$wiki_cat2_id = $row['cat2_id'];
		$wiki_text = $row['text'];
		$wiki_pending = $row['pending'];
		$wiki_hackerid = $row['hacker_id'];
		$wiki_date = $row['date'];
		$wiki_lastchangehackerid = $row['lastchange_hackerid'];
		$wiki_lastchangedate = $row['lastchange_date'];
		if ($wiki_pending == 1) return "This article can not be edited as it is currently is pending approval.";
	}

	if (EP2Level(GetHackerEP($hackerdata['id'])) < $wiki_level && !$is_staff) return "You must be at least level $wiki_level before you can edit a Wiki article.";
		
  	// other changes pending?
	$result = mysqli_query($link, "SELECT id FROM wiki WHERE title = '$wiki_title' AND pending = 1");
	if (mysqli_num_rows($result) > 0) return "You can not edit this article. There is already a change pending for it.";
?>
	<div class="col w80 ">
		<h2>Edit wiki article</h2>
		<form method="POST" action="" name="hf_form">
			<input type="hidden" name="h" value="dowiki">
			<input type="hidden" name="wiki_id" value="<?php echo $wiki_id; ?>">
			Category #1: <select name="wiki_cat1_id">
			<?php
				$result2 = mysqli_query($link, "SELECT * FROM wikicat ORDER BY name ASC");
				while ($row2 = mysqli_fetch_assoc($result2)) {
					$selected = '';
					if ($wiki_id > 0) if ($wiki_cat1_id == $row2['id']) $selected = " SELECTED";
						echo '<OPTION VALUE="'.$row2['id'].'"'.$selected.'>'.$row2['name'];
				}
			?>
			</select><br>

			Category #2: <select name="wiki_cat2_id">
			<?php
				echo '<OPTION VALUE="0" SELECTED>--'; // the second category is optional
				$result2 = mysqli_query($link, "SELECT * FROM wikicat ORDER BY name ASC");
				while ($row2 = mysqli_fetch_assoc($result2)) {
					$selected = '';
					if ($wiki_id > 0) if ($wiki_cat2_id == $row2['id']) $selected = " SELECTED";
						echo '<OPTION VALUE="'.$row2['id'].'"'.$selected.'>'.$row2['name'];
				}
			?>
			</select><br>

			Title: <input type="text" name="wiki_title" size="50" maxlength="50" value="<?php echo $wiki_title; ?>"><br>
			Article: <textarea name="wiki_text" class="w100i h450"><?php echo br2nl($wiki_text); ?></textarea><br>
			<input type="submit" value="Submit">
		</form>
	</div>
	<div class="col w20">
		<?php printBBC("100%"); ?>
	</div>
