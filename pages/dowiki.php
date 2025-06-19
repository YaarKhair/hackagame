<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include ("modules/permissions.php");

	// we need a title before we can do anything
	if (!empty($_REQUEST['wiki_title'])) {
		$wiki_title = sql($_REQUEST['wiki_title']);
		$wiki_title = preg_replace("/[^A-Za-z0-9 ]/", '', $wiki_title);
	}
	else return "The title can not be empty.";

	// still here? then you're adding a new or updating an existing
	if (!empty($_POST['wiki_id'])) $wiki_id = intval($_POST['wiki_id']);
	else $wiki_id = 0;
	
    if (!empty($_POST['wiki_cat1_id'])) $wiki_cat1_id = intval($_POST['wiki_cat1_id']);
	else return "You must specify at least 1 category.";
    if (!empty($_POST['wiki_cat2_id'])) $wiki_cat2_id = intval($_POST['wiki_cat2_id']);
    else $wiki_cat2_id = 0;

    $wiki_text = "";
	if (!empty($_POST['wiki_text'])) {
		$wiki_text = $_POST['wiki_text'];
		$wiki_text = preg_replace('#\r?\n#', '[br]', $wiki_text);
		$wiki_text = sql($wiki_text, false);
		$wiki_text = str_replace("[br]", "<br>", $wiki_text);
	}
	else return "The wiki text can not be empty.";
	
	// are you allowed to edit?
	if (EP2Level(GetHackerEP($hackerdata['id'])) < $wiki_level && !$is_staff) return "You must be at least level $wiki_level before you can edit a Wiki article.";

	// new article or revision?
	if ($wiki_id == 0) {
		// new
		$result = mysqli_query($link, "INSERT INTO wiki (title, text, hacker_id, cat1_id, cat2_id, date, lastchange_hackerid, lastchange_date, pending) VALUES ('$wiki_title', '$wiki_text', {$hackerdata['id']}, $wiki_cat1_id, $wiki_cat2_id, '$now', ".$hackerdata['id'].", '$now', 1)");
		PrintMessage ("Success", "Wiki article posted. It's now pending approval from the Game Administration. As soon as the wiki article is approved it will apear in the wiki.", "40%");
	}
	else {
		$result = mysqli_query($link, "SELECT pending, hacker_id, date FROM wiki WHERE id = $wiki_id");
		if (mysqli_num_rows($result) == 0) return "Invalid ID";
		$row = mysqli_fetch_assoc($result);

		if ($row['pending'] == 1) return "There is already a change pending for this article.";

		// revision
		$result = mysqli_query($link, "INSERT INTO wiki (title, text, hacker_id, cat1_id, cat2_id, date, lastchange_hackerid, lastchange_date, pending, revision_id) VALUES ('$wiki_title', '$wiki_text', {$row['hacker_id']}, $wiki_cat1_id, $wiki_cat2_id, '{$row['date']}', {$hackerdata['id']}, '$now', 1, $wiki_id)");
		PrintMessage ("Success", "Wiki article updated. It's now pending approval from the Game Administration.", "40%");
	}
?>