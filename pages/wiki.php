<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<script type="text/javascript" language="JavaScript"><!--
    function ReverseDisplay(d) {
        if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
        else { document.getElementById(d).style.display = "none"; }
    }
//--></script>
<?php
	// permissiongroups
	include("modules/permissions.php");

	// search? then we need to show a different category tree
	$search = "";
	if (!empty($_REQUEST['search'])) $search = sql($_REQUEST['search']);

	// if you supplied a title you're opening a wiki article for reading
	$wiki_title = "";
	if (!empty($_REQUEST['title'])) $wiki_title = sql($_REQUEST['title']);

	if ($wiki_title != "") {
		$result = mysqli_query($link, "SELECT * FROM wiki WHERE title = '$wiki_title' AND pending = 0");
		if (mysqli_num_rows($result) == 0) return "Wiki article not found. You could <a href=\"?h=editwiki&title=$wiki_title\">write it yourself</a>.";
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
			if ($wiki_pending == 1) $wiki_text = "This article is pending approval.";
		}
	}
	else {
		$wiki_title = "Welcome to the HackerForever Wiki";
		$wiki_text = "Welcome to the HackerForever Wiki. You can use the category tree on the left to navigate or use the search to find specific Wiki pages. If a Wiki page is missing, consider creating it yourself.";
		$wiki_cat1_id = 0;
		$wiki_hackerid = 1;
		$wiki_date = $now;
		$wiki_lastchangehackerid = 1;
		$wiki_lastchangedate = $now;
	}
?>
	<!-- Search form -->
	<div class="row ">
		<form method="GET" action="index.php">
			<input type="hidden" name="h" value="wiki">
			Search phrase: <input type="text" name="search" size="40" maxlength="50">
			<input type="submit" value="Search wiki">
		</form>
<?php if (EP2Level(GetHackerEP($hackerdata['id'])) >= $wiki_level || $is_staff) { ?>
		<form method="GET" action="index.php">
			<input type="hidden" name="h" value="editwiki">
			Title: <input type="text" name="title" size="40" maxlength="50">
			<input type="submit" value="Write Wiki Article">
		</form>		
<?php } ?>
	</div>

	<div class="row mv10">
		
		<div class="col w30 dark-bg">
		<?php 	
			if ($search == "") {
				echo "<h2>HackerForever Wiki</h2>";

				// list all categories!
				$result = mysqli_query($link, "SELECT * FROM wikicat ORDER BY name ASC"); // read all categories
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<a href=\"javascript:ReverseDisplay('{$row['id']}')\">{$row['name']}</a><br>";

					$wiki_list = '';
					$result2 = mysqli_query($link, "SELECT * FROM wiki WHERE (cat1_id = {$row['id']} OR cat2_id = {$row['id']}) AND pending = 0 ORDER BY title ASC"); // read all articles of current category
					if (mysqli_num_rows($result2) == 0) $wiki_list = "&nbsp;&nbsp;&nbsp;&nbsp;No wiki articles found.<br>";
					else
						while ($row2 = mysqli_fetch_assoc($result2))
							$wiki_list .= "&nbsp;&nbsp;|_&nbsp;[[".$row2['title']."]]<br>";
					echo '<div id="'.$row['id'].'" style="display:none;">'.replaceBBC($wiki_list).'<br></div>'; // show the list of articles in this category
				}
			}
			else {
				echo "<h2>Wiki Search Results</h2>";
				// search results
				$search_words = explode (" ", $search);
				$search_query = "((title = '$search') OR ";
		
				$counter = 0;
				foreach ($search_words as $word)
				{
					$search_query .= "(text LIKE '% $word%' OR text LIKE '%$word %' OR title LIKE '%$word%')";
					$counter ++;
					if ($counter < count($search_words)) $search_query .= " AND ";
				}

				$search_query .= ")";
				//echo $search_query; die;
				echo "Search results<br>";
				$wiki_list = '';

				$result2 = mysqli_query($link, "SELECT * FROM wiki WHERE $search_query AND pending = 0 ORDER BY title ASC"); // find results for search query
				if (mysqli_num_rows($result2) == 0) $wiki_list = "Your search did not return any results.";
				else 
					while ($row2 = mysqli_fetch_assoc($result2))
						$wiki_list .= "&nbsp;&nbsp;|_&nbsp;[[".$row2['title']."]]<br>";
				
				echo '<div id="searchresults" style="display:block;">'.replaceBBC($wiki_list).'<br></div>'; // show the list of articles in this category
			}
		?>
		</div>
		<div class="col w5">
			<!-- just here for spacing //-->
			&nbsp;
		</div>
		<div class="col w65 dark-bg">
			<h2>Wiki: <?php echo $wiki_title; ?></h2>
			<?php 
				// Actual text
				echo ReplaceBBC($wiki_text).'<br><br>';

				// Notes
				echo '<div class="note">Written by: '.mysqli_get_value("alias", "hacker", "id", $wiki_hackerid).'@'.Number2Date($wiki_date).'<br>Last change: '.mysqli_get_value("alias", "hacker", "id", $wiki_lastchangehackerid).'@'.Number2Date($wiki_lastchangedate).'</div>'; //<br>Tags: '.$wiki_tags.'

				// Edit
				echo '[<a href="?h=editwiki&title='.URL($wiki_title).'">Edit article</a>]';

				// Delete
				if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2))
				echo ' [<a href="?h=deletewiki&wiki_title='.URL($wiki_title).'" onClick="return confirmSubmit(\'Are you sure you want to delete this wiki article?\')">Delete article</a>]';

				// Unfold category
				if ($wiki_cat1_id > 0) echo '<script type="text/javascript">ReverseDisplay('.$wiki_cat1_id.');</script>';
			?>
		</div>
</div>
