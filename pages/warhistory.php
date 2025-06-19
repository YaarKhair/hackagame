<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php

	// Select last 5 wars
	$result = mysqli_query($link, "SELECT victim_clanid, attacker_clanid, victim_points, attacker_points, end_date FROM war WHERE end_date > 0 AND active = 0 AND (attacker_points = 0 OR victim_points = 0) ORDER BY end_date DESC LIMIT 0,10");
	
	// Loop through the records
	$wars = array();
	while($row = mysqli_fetch_assoc($result)) {
		
		// Find out the winner
		if($row['victim_points'] == 0) {
			$winner = $row['attacker_clanid'];
			$winner_points = $row['attacker_points'];
			$loser = $row['victim_clanid'];
			$loser_points = $row['victim_points'];
		} else if($row['attacker_points'] == 0) {
			$winner = $row['victim_clanid'];
			$winner_points = $row['victim_points'];
			$loser = $row['attacker_clanid'];
			$loser_points = $row['attacker_points'];
		} else continue;
		
		$wars[] = array("winner" => $winner, "loser" => $loser, "winner_points" => $winner_points, "loser_points" => $loser_points, "end_date" => $row['end_date']);
	}

?>
	<h1>War History (No surrenders)</h1>
	<div class="row th light-bg">
		<div class="col w33">Winner</div>
		<div class="col w33">Loser</div>
		<div class="col w33">End Date</div>
	</div>
	<div class="dark-bg">

<?php 
	foreach($wars as $war) {
		echo "<div class='row mv10'>";
			echo "<div class='col w33'><span class='good'>".mysqli_get_value("alias", "clan", "id", $war['winner'])."</span> [{$war['winner_points']}]</div>";
			echo "<div class='col w33'><span class='bad'>".mysqli_get_value("alias", "clan", "id", $war['loser'])."</span> [{$war['loser_points']}]</div>";
			echo "<div class='col w33'>".Number2Date($war['end_date'])."</div>";
		echo "</div>";
	}
	echo "</div>";
?>
</table>