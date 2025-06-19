<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$action = "view";
	
	if (!empty($_GET['action'])) $action = sql($_GET['action']);
	if (!empty($_GET['id'])) $wiki_id = intval($_GET['id']);
	else return "ID empty. Bad parameter.";

	if ($action == "approve") {
		// new article or revision?
		$result = mysqli_query($link, "SELECT * FROM wiki WHERE id = $wiki_id");
		if (mysqli_num_rows($result) == 0) return "Wiki article not found.";
		else {
			$row = mysqli_fetch_assoc($result);
			if ($row['revision_id'] > 0) $result = mysqli_query($link, "DELETE FROM wiki WHERE id = {$row['revision_id']}"); // delete orginal if it what we are approving is a modification
			$result = mysqli_query($link, "UPDATE wiki SET pending = 0 WHERE id = $wiki_id"); // remove pending status
			PrintMessage ("Success", "Wiki article appoved. <a href=\"?h=wiki&title=".URL($row['title'])."\">View</a>", "40%");
		}
	}	
	if ($action == "disapprove") {
		$result = mysqli_query($link, "DELETE FROM wiki WHERE id = $wiki_id"); // delete pending article
		PrintMessage ("Success", "Wiki article disappoved.", "40%");
	}	
	if ($action == "view") {
		$result = mysqli_query($link, "SELECT * FROM wiki WHERE id = $wiki_id");
		if (mysqli_num_rows($result) == 0) return "Wiki article not found.";
		
		// view the brand new article
		$row = mysqli_fetch_assoc ($result);
		$wiki_id = $row['id'];
		$wiki_title = $row['title'];
		$wiki_text = $row['text'];
		$cat1 = ID2Cat($row['cat1_id']);
		$cat2 = ID2Cat($row['cat2_id']);
		//$wiki_tags = $row['tags'];

		if ($row['revision_id'] > 0) {	
			// view the changes made to the original
			$result2 = mysqli_query($link, "SELECT * FROM wiki WHERE id = {$row['revision_id']}");
			$row2 = mysqli_fetch_assoc ($result2); // original record
			$wiki_title_old = $row2['title'];
			$wiki_text_old = $row2['text'];
			$cat1_old = ID2Cat($row2['cat1_id']);
			$cat2_old = ID2Cat($row2['cat2_id']);
			//$wiki_tags_old = $row2['tags'];
			
			/* HIGHLIGHT CHANGES */
			
			// title
			$wiki_title = compareWiki($wiki_title_old, $wiki_title);
			
			// text changes
			$wiki_text = nl2br(compareWiki(br2nl($wiki_text_old), br2nl($wiki_text)));
			
			// tags changes
			//$wiki_tags = compareWiki($wiki_tags_old, $wiki_tags);
			
			// category changes
			if ($cat1_old != $cat1) $cat1 = compareWiki($cat1_old, $cat1);
			if ($cat2_old != $cat2) $cat2 = compareWiki($cat2_old, $cat2);
		}
        
?>        
	<div class="col w65 dark-bg">
		<h2>Wiki: <?php echo $wiki_title; ?></h2>
		<b>Categorie: <?php echo "$cat1, $cat2"; ?></b><br><br>
<?php
		echo ReplaceBBC($wiki_text).'<br><br>';
		//echo "Tags: $wiki_tags<br><br>"	;
		echo '[<a href="?h=domodwiki&id='.$wiki_id.'&action=approve">Approve changes</a>] [<a href="?h=domodwiki&id='.$wiki_id.'&action=disapprove">Disapprove changes</a>]';
	}	
?>