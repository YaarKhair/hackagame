<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$date = date($date_format, strtotime("-24 hours"));
 	$result = mysqli_query($link, "SELECT id, bot_score FROM hacker WHERE banned_date = 0 AND active =  1 ORDER by bot_score DESC LIMIT 50");
 	echo '
		<table>
			<caption>Players with the highest Bot Score</caption>
			<thead>
				<tr>
					<th>Hacker</th>
					<th>Bot Score</th>
				</tr>
			</thead>
			<tbody>'; 	
			
 	
 	while ($row = mysqli_fetch_assoc($result)) {
 		echo "<tr><td>".ShowHackerAlias($row['id'])."</td><td>{$row['bot_score']}</td></tr>";
 	}
 	echo '</tbody></table>';
?>