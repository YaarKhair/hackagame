<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include ("modules/permissions.php");

	// we need a title before we can do anything
	if (!empty($_REQUEST['wiki_title'])) {
		$wiki_title = sql($_REQUEST['wiki_title']);
		$wiki_title = preg_replace("/[^A-Za-z0-9 ]/", '', $wiki_title);
	}
	else return "The title can not be empty.";

	if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) {
		$result = mysqli_query($link, "DELETE FROM wiki WHERE title = '$wiki_title'");
		return "Wiki article $wiki_title removed.";
	}
	else AddLog($hackerdata['id'], "hacker", "abuse", "Tried to delete a wiki page"); die();
?>