<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$_SESSION['loaded'] = 0; // anti refresh
	$timeleft = 0;
	// see if we are in prison
	if (IsImprisoned($hackerdata['id'])) {
		// lets get some details
		$query = "SELECT prison_till, prison_reason FROM hacker WHERE id = ".$hackerdata['id'];
		$result = mysqli_query($link, $query);
		$row = mysqli_fetch_assoc($result);
		
		$timeleft = SecondsDiff($now,$row['prison_till']); // how much time is left in seconds
		$_SESSION['countdown'] = $timeleft;
		PrintMessage("Imprisoned", "<img src=\"images/prison.jpg\" class=\"frame rounded\"><br><br>You were put behind bars because you violated the law.<br><br><strong>You are in prison. Your computer has been confiscated and your bankaccount emptied.<br><br>The official charge is:</strong> ".$row['prison_reason']."<br><br>Prison time remaining: <span id=\"countdown\">0</span>&nbsp;seconds.", "100%");
	}
	else {
		echo '
			<h2>Currently in Prison</h2>
				<div class="row th light-bg">
					<div class="col w50">Hacker</div>
					<div class="col w50">Prisontime left</div>
				</div>';
			$query = "SELECT id, clan_id, prison_till FROM hacker WHERE prison_from <= '$now' AND prison_till >= '$now' AND alias != ''"; // retired npcś have an empty alias
			$result = mysqli_query($link, $query);
			if (mysqli_num_rows($result) == 0) echo '<div class="row">'.PrintMessage("Info", "No hackers in prison currently.").'</div>';
			else {
				echo '<div class="dark-bg">';
				while ($row = mysqli_fetch_assoc($result)) {
					echo '
						<div class="row hr-light">
							<div class="col w50">'.ShowHackerAlias($row['id'], $row['clan_id']).'</div>
							<div class="col w50">'.Seconds2Time(SecondsDiff($now,$row['prison_till'])).'</div>
						</div>';
				}
				echo '</div>';
			}
		echo '
		<br><br>';	
		
		// list the 10 most recent prisontimes
		echo '
			<h2>Recently in Prison</h2>
				<div class="row th light-bg">
					<div class="col w50">Hacker</div>
					<div class="col w50">Released on</div>
				</div>
			<tbody>';
		$query = "SELECT id, clan_id, prison_till FROM hacker WHERE prison_till < ".$now." AND prison_till <> 0 AND alias != '' ORDER by prison_till DESC LIMIT 10";
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) echo '<div class="row">No recent prisoners found..</div>';
			echo '<div class="dark-bg">';
		while ($row = mysqli_fetch_assoc($result)) {
			echo '
				<div class="row hr-light">
					<div class="col w50">'.ShowHackerAlias($row['id'], $row['clan_id']).'</div>
					<div class="col w50">'.Number2Date($row['prison_till']).'</div>
				</div>';
		}
			echo '</div>';
	}	
?>
