<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$hacker_id = intval($_GET['id']);
	// total in game money for the past 20 days
	$result = mysqli_query($link, "SELECT date, details FROM log WHERE event = 'interval' AND hacker_id = $hacker_id ORDER by date ASC");
	$previous_date = 0;
	
	echo '
		<table width="50%">
			<caption>SHIT - Small Hack Interval Tool for '.ShowHackerAlias($hacker_id, 0, false, true).' [<a href="?h=doadminaction&action=resetshit&id='.$hacker_id.'">Reset</a>]</caption>
			<thead>
				<tr>
					<th>Date</th>
					<th>Page</th>
					<th>Interval (seconds)</th>
				</tr>
			</thead>
			<tbody>';
	while ($row = mysqli_fetch_assoc($result)) {
		echo "<tr>";
		echo "<td>".Number2Date($row['date'])."</td>";
		echo "<td>".$row['details']."</td>";
		$interval = SecondsDiff($previous_date, $row['date']);
		// calculate the total
		echo "<td>$interval</td>";
		echo "</tr>";
		$previous_date = $row['date'];
	}		
	echo "</tbody></table>";
?>