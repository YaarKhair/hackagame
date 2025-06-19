<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php	
	$result = mysqli_query($link, "SELECT id, title, lastchange_date, lastchange_hackerid FROM wiki WHERE pending = 1");
	$pending = mysqli_num_rows($result);
	echo '
		<table width="100%">
			<caption>Pending Wiki Articles (total:'.$pending.')';
	if (mysqli_num_rows($result) > 0) {		
		echo '<thead>
				<tr>
					<th>Title</th>
					<th>Date</th>
					<th>Hacker</th>
				</tr>
			</thead>
			<tbody>';
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<tr> ';
			echo '<td><a href="?h=domodwiki&id='.$row['id'].'">'.$row['title'].'</a></td>';
			echo '<td>'.Number2Date($row['lastchange_date']).'</td>';
			echo '<td>'.ShowHackerAlias($row['lastchange_hackerid']).'</td>';
			echo '</tr>';
		}
	}
	else echo '<tr><td colspan="3">No changes pending...</td></tr>';
	echo '</tbody></table>';
?>