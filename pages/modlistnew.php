<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php	
	$result = mysqli_query($link, "SELECT id, started, email, real_ip, activationcode, referral_id FROM hacker WHERE npc = 0 ORDER BY started DESC LIMIT 50");
	echo '
		<table width="100%">
			<caption>Last 50 Signups';
	if (mysqli_num_rows($result) > 0) {		
		echo '<thead>
				<tr>
					<th>Alias</th>
					<th>System</th>
					<th>Joined</th>
					<th>Activated</th>
					<!--<th>Email</th>//-->
					<th>Referral</th>
					<th>IP</th>
				</tr>
			</thead>
			<tbody>';
		while ($row = mysqli_fetch_assoc($result)) {
			if ($row['activationcode'] != "")
				$activated = '<span class="bad">No</span>';
			else
				$activated = '<span class="good">Yes</span>';
				
			$referral = 'None';
			if($row['referral_id'] > 0) $referral = mysqli_get_value("alias", "hacker", "id", $row['referral_id']);
			echo '<tr>';
			echo '<td>'.ShowHackerAlias($row['id']).'</td>';
			echo '<td>'.GetStatus($row['id']).'</td>';
			echo '<td>'.Number2Date($row['started']).'</td>';
			echo '<td>'.$activated.'</td>';
			echo '<td>'.$referral.'</td>';
			//echo '<td>'.$row['email'].'</td>';
			echo '<td>'.$row['real_ip'].'</td>';
			echo '</tr>';
		}
	}	
	echo '</tbody></table>';
?>
