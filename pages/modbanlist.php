<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php	
	$result = mysqli_query($link, "SELECT id, banned_date, banned_reason FROM hacker WHERE banned_date > 0 ORDER BY banned_date DESC");
	$banned = mysqli_num_rows($result);
	echo '
		<table width="100%">
			<caption>BANNED PLAYERS (total:'.$banned.')';
	if (mysqli_num_rows($result) > 0) {		
		echo '<thead>
				<tr>
					<th>Alias</th>
					<th>Date</th>
					<th>Reason</th>
				</tr>
			</thead>
			<tbody>';
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<tr>';
			echo '<td>'.ShowHackerAlias($row['id']).'</a></td>';
			echo '<td>'.Number2Date($row['banned_date']).'</td>';
			echo '<td>'.$row['banned_reason'].'</td>';
			echo '</tr>';
		}
	}	
	echo '</tbody></table>';
?>