<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$error = "";
	$limit = 50;
	
	$result = mysqli_query($link, "SELECT * FROM modnote INNER JOIN hacker ON modnote.hacker_id = hacker.id WHERE hacker.banned_date = 0 ORDER BY date DESC LIMIT $limit");
	
	if (mysqli_num_rows($result) == 0) {
		PrintMessage ("error", "There are no mod notes...");
	}
	else {
		echo '
			<table width="100%">
				<caption>The '.$limit.' most recent mod-notes (excl. banned players)</caption>
				<thead>
					<th>Player</th>
					<th>Mod</th>
					<th>Note</th>
					<th>Date</th>
				</thead>
				<tbody>';
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<tr>
					<td>'.ShowHackerAlias($row['hacker_id']).'</td>
					<td>'.ShowHackerAlias($row['creator_id']).'</td>
					<td>'.substr(str_replace("<br>", " ", $row['message']), 0, 50).'...</td>
					<td>'.Number2Date($row['date']).'</td></tr>';
		}
		echo '</tbody></table>';
	}
?>
