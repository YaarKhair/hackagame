<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php	
	$result = mysqli_query($link, "SELECT hacker.id as hacker_id, hacker.started as hacker_started, referal.id as referal_id, referal.started as referal_started FROM hacker LEFT JOIN hacker referal ON hacker.referral_id = referal.id WHERE hacker.referral_id > 0 ORDER BY hacker.started DESC");
	$total = mysqli_num_rows($result);
	echo '
		<table width="100%">
			<caption>REFERED PLAYERS (total:'.$total.')';
	if (mysqli_num_rows($result) > 0) {		
		echo '<thead>
				<tr>
					<th>Alias</th>
					<th>Joined</th>
					<th>Refered by</th>
					<th>Joined</th>
				</tr>
			</thead>
			<tbody>';
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<tr>';
			echo '<td>'.ShowHackerAlias($row['hacker_id']).'</a></td>';
			echo '<td>'.Number2Date($row['hacker_started']).'</td>';
			echo '<td>'.ShowHackerAlias($row['referal_id']).'</a></td>';
			echo '<td>'.Number2Date($row['referal_started']).'</td>';
			echo '</tr>';
		}
	}	
	echo '</tbody></table>';
?>