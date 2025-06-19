<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// Trying to view your own achievements or someone else's?
	if (!empty($_GET['hacker_id'])) {
		$id = intval($_GET['hacker_id']);
		$result = mysqli_query($link, "SELECT * FROM hacker WHERE id = $id");
		if (mysqli_num_rows($result) == 0) return "Hacker not found.";
		$row = mysqli_fetch_assoc($result);
		if ($row['publicstats'] == 0 && !InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) return "These stats are not public.";
	} else {
		$row = array();
		$row = $hackerdata;
	}

	// All achievements
	$result2 = mysqli_query($link, "SELECT * FROM achievement ORDER BY ID ASC");
	echo "<h1>Achievements</h1>";
	echo "<div class='row th light-bg'>
				<div class='col w30'>Achievement</div>
				<div class='col w30'>Progress</div>
				<div class='col w30'>Reward</div>
		  </div>
		  <div class='dark-bg'>";
	
	while($row2 = mysqli_fetch_assoc($result2)) {
		$increment = $row2['increment'];
		if($row2['name'] == 'Level') $points = EP2Level($row['ep']);
		else $points = $row[$row2['hacker_field']];
		$tier = floor($points / $increment); // round down
		$next_tier = $tier+1;
		$next_tier_points = $next_tier * $increment;
		$ep_reward = $row2['ep_reward'] * $next_tier;
		$skill_reward = $row2['skill_reward'] * $next_tier;
		$cash_reward = $row2['cash_reward'] * $next_tier;
		
		echo "<div class='row hr-light'>
				<div class='col w30'>{$row2['name']} | Tier #$tier</div>
				<div class='col w30'>".ShowProgress($points, $next_tier_points, 'PROGRESS')." [$points / $next_tier_points]</div>
				<div class='col w30'>EP: ".number_format($ep_reward)." | Skill: ".number_format($skill_reward)." | Cash: $".number_format($cash_reward)."</div>
			</div>";
	}
		echo "</div>";

?>