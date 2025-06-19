<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// show the FBI most wanted list
	echo '
	<h1><img src="images/fbi_small.png" title="FBI" align="right" />FBI Most Wanted Cybercriminals</h1>
	The people on this list are wanted for various cyber crimes.<br>
	If you have any information on these individuals,<br>please contact the FBI HotLine: 0900 SNITCH<br>
	<br>
	<br>
	<br>
			<div class="row th light-bg">
				<div class="col w20">#</div>
				<div class="col w40">Photo</div>
				<div class="col w20">Last known alias</div>
				<div class="col w20">Wanted since</div>
			</div>
			<div class="dark-bg">
	';	
	$result = mysqli_query($link, "SELECT id, fbi_wanteddate FROM hacker WHERE fbi_wanteddate > 0 AND (npc = 0 OR npc = {$hackerdata['id']}) ORDER BY ep DESC");
	if (mysqli_num_rows($result) == 0) {
		echo '<div id="row">List is empty.</div>';
	}
	else {
		$counter = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$counter ++;
			echo '<div class="row hr-light"><div class="col w20">'.$counter.'</div>';
			echo '<div class="col w40">'.ShowAvatar($row['id']).'</div>';
			echo '<div class="col w20">'.ShowHackerAlias($row['id'], 1).'</div>';
			echo '<div class="col w20">'.Number2Date($row['fbi_wanteddate']).'</div></div>';
		}
	}
	echo "</div>";
?>