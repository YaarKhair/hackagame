<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!empty($_GET['hacker_id'])) {
		$id = intval($_GET['hacker_id']);
		$result = mysqli_query($link, "SELECT * FROM hacker WHERE id = $id");
		if (mysqli_num_rows($result) == 0) return "Hacker not found.";
		$row = mysqli_fetch_assoc($result);
		if ($row['publicstats'] == 0 && (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2))) return "These stats are not public.";
	}
	else {
		$row = Array();
		$row = arrayCopy ($hackerdata);
		$id = $hackerdata['id'];
	}	
	
	// days playing
	$seconds_playing = SecondsDiff($row['started'], $now);
	$days_playing = (($seconds_playing / 60) / 60) / 24;
	
	// ep gained in last 24 hours
	$date = date($date_format, strtotime("-24 hours"));
 	$result2 = mysqli_query($link, "SELECT details FROM log WHERE event = 'ep' AND date > '$date' AND hacker_id = $id");
 	$total_ep = 0;
 	while ($row2 = mysqli_fetch_assoc($result2)) {
 		$ep_split1 = explode (":", $row2['details']);
 		$ep_split2 = explode("(", $ep_split1[1]);
 		$ep = intval($ep_split2[0]);
 		$total_ep += $ep;
 	}
	// show ep/skill log only if you're on your own stats page.
 	$ep_history_link = '';
	$skill_history_link = '';
	$infections_link = '';
	if ($id == $hackerdata['id']) {
		$ep_history_link = '&nbsp;[<a href="?h=history&type=ep">History</a>]';
		$skill_history_link = '&nbsp;[<a href="?h=history&type=skill">History</a>]';
		$hp_history_link = '&nbsp;[<a href="?h=history&type=hp">History</a>]';
		$infections_link = '&nbsp;[<a href="?h=infections#infectedservers">view infected</a>]';
	}

		echo '
				<h1>Personal Stats</h1>
					<div class="row th light-bg">
						<div class="col w50">Title</div>
						<div class="col w50">Stat</div>
					</div>
					<div class="dark-bg">
					<div class="row hr-light">
						<div class="col w50">Membership</div>
						<div class="col w50">'.number_format($days_playing).'&nbsp;day(s) since '.number2date($row['started']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Premium until</div>
						<div class="col w50">'.number2date($row['donator_till']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">EP</div>
						<div class="col w50">'.number_format($row['ep']).$ep_history_link.'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">EP gained in past 24 hours</div>
						<div class="col w50">'.number_format($total_ep).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Level</div>
						<div class="col w50">'.EP2Level(GetHackerEP($row['id'])).'</div>
					</div>
					<div class="row hr-light">
						<div class="col w50">Next Level EP</div>
						<div class="col w50">'.number_format(GetNextLevelEP($row['id'])).'</div>
					</div>
					<div class="row hr-light">
						<div class="col w50">Next Level Progress</div>
						<div class="col w50">'.ShowProgress(EP2LevelProgress($row['id']),100, "PROGRESS").'</div>
					</div>
					<div class="row hr-light">
						<div class="col w50">Total Level Progress</div>
						<div class="col w50">'.ShowProgress(EP2Level(GetHackerEP($row['id'])),$maxlevel, "PROGRESS").'</div>
					</div>
					<div class="row hr-light">
						<div class="col w50">Avg. EP gain / day</div>
						<div class="col w50">'.number_format($row['ep'] / $days_playing).'</div>
					</div>
					<div class="row hr-light">
						<div class="col w50">Skill</div>
						<div class="col w50">'.number_format($row['skill']).$skill_history_link.'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Skill Progress</div>
						<div class="col w50">'.ShowProgress($row['skill'] -(($row['convention_last']-1) * $skillslots_per_convention), $skillslots_per_convention, "PROGRESS").'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Total Skill Progress</div>
						<div class="col w50">'.ShowProgress($row['skill'],$maxskill, "PROGRESS").'</div>
					</div>
					<div class="row hr-light">
						<div class="col w50">Avg. Skill gain / day</div>
						<div class="col w50">'.number_format($row['skill'] / $days_playing).'</div>
					</div>
					<div class="row hr-light">
						<div class="col w50">Hackpoints</div>
						<div class="col w50">'.number_format($row['hackpoints']).' ('.number_format($row['hackpoints_credit']).' spendable)'.$hp_history_link.'</div>
					</div>
					<div class="row hr-light">
						<div class="col w50">World Rank (EP)</div>
						<div class="col w50">#'.GetWorldRankEP($row['id']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">World Rank (HP)</div>
						<div class="col w50">#'.GetWorldRankHP($row['id']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">PCs hacked (success/fail)</div>
						<div class="col w50">'.number_format($row['pchack_win']).'/'.number_format($row['pchack_fail']).' ('.@round($row['pchack_win'] / ($row['pchack_win'] + $row['pchack_fail']) * 100).'%)</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Servers Owned</div>
						<div class="col w50">'.NumServers($row['id']).'/'.MaxServers($row['id']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Servers hacked (success/fail)</div>
						<div class="col w50">'.number_format($row['serverhack_win']).'/'.number_format($row['serverhack_fail']).' ('.@round($row['serverhack_win'] / ($row['serverhack_win'] + $row['serverhack_fail']) * 100).'%)</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Servers (infected/killed)</div>
						<div class="col w50">'.number_format($row['serversinfected']).'/'.number_format($row['serverskilled']).$infections_link.'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Contracts (success/fail)</div>
						<div class="col w50">'.number_format($row['contract_win']).'/'.number_format($row['contract_fail']).' ('.@round($row['contract_win'] / ($row['contract_win'] + $row['contract_fail']) * 100).'%)</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">FBI hacks (success/fail)</div>
						<div class="col w50">'.number_format($row['fbihack_win']).'/'.number_format($row['fbihack_fail']).' ('.@round($row['fbihack_win'] / ($row['fbihack_win'] + $row['fbihack_fail']) * 100).'%)</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Gateway hacks (success/fail)</div>
						<div class="col w50">'.number_format($row['gwhack_win']).'/'.number_format($row['gwhack_fail']).' ('.@round($row['gwhack_win'] / ($row['gwhack_win'] + $row['gwhack_fail']) * 100).'%)</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Bounties collected</div>
						<div class="col w50">'.number_format($row['bountiescollected']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Bounties total</div>
						<div class="col w50">'.$currency.number_format($row['bountiestotal']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">FTP sales</div>
						<div class="col w50">'.number_format($row['ftpsales']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Imprisoned</div>
						<div class="col w50">'.number_format($row['imprisoned']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Jailed</div>
						<div class="col w50">'.number_format($row['jailed']).'</div>
					</div>	
					<div class="row hr-light">
						<div class="col w50">Bails</div>
						<div class="col w50">'.number_format($row['bails']).'</div>
					</div>	
    				<div class="row hr-light">
						<div class="col w50">Bust outs (success/fail)</div>
						<div class="col w50">'.number_format($row['bust_win']).'/'.number_format($row['bust_fail']).' ('.@round($row['bust_win'] / ($row['bust_win'] + $row['bust_fail']) * 100).'%)</div>
					</div>	
    				<div class="row hr-light">
						<div class="col w50">CTF captures (success/fail)</div>
						<div class="col w50">'.number_format($row['ctf_win']).'/'.number_format($row['ctf_fail']).' ('.@round($row['ctf_win'] / ($row['ctf_win'] + $row['ctf_fail']) * 100).'%)</div>
					</div>	
    				<div class="row hr-light">
						<div class="col w50">Gambling</div>
						<div class="col w50">Won: '.$currency.number_format($row['gamble_won']).'<br>Lost: '.$currency.number_format($row['gamble_lost']).'<br>Earnings: '.$currency.number_format(@round($row['gamble_won'] - $row['gamble_lost'])).'</div>
					</div>
					</div>';	
?>
