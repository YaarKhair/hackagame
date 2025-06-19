<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    $_SESSION['dojail'] = 1; // anti refresh
    $output = '';
	$timeleft = 0;
	$bribe_form = '';
    if ($hackerdata['nextbribe_date'] < $now) {
        $bribe = $currency.number_format($hackerdata['jailed_bail'] * 1.5);
        $bribe_form = "You can bribe the police to get out with a 50% chance of him accepting. If he does not your bail will be doubled.<br><form action='index.php' method='POST'><input type='hidden' name='h' value='dojail'><input type='hidden' name='action' value='bribe'><input type='submit' value='Bribe ($bribe)'></form><br><br>";
    }    
	// see if we are in jail
	if (IsJailed($hackerdata['id'])) {
		$timeleft = SecondsDiff($now,$hackerdata['jailed_till']); // how much time is left in seconds
		$_SESSION['countdown'] = $timeleft;
?>		
		<img src="images/jail.jpg" class="frame rounded"><br><br>
		You were put behind bars because you violated the law.<br><br>
		<strong>You are in jail because:</strong> <?php echo $hackerdata['jailed_reason']; ?><br><br>
		Jail time remaining: <span id="countdown">0</span><br>Bail is set to <?php echo $currency.number_format($hackerdata['jailed_bail']); ?>.<br><br>
		<?php echo $bribe_form; ?>
		While in jail, <a href="?h=chat">you can chat</a>, <a href="?h=forum">visit the forums</a> or <a href="?h=games">play a game</a> using the public jail computers.
<?php
	}
	else {
		echo '
			<h2>Currently in Jail</h2>
				<div class="row th light-bg">
					<div class="col w20">Hacker</div>
					<!--<div class="col w10">Level</div>//-->
					<div class="col w30">Reason</div>
					<div class="col w20">Jailtime left</div>
					<div class="col w10">Bail</div>
					<div class="col w20">Action</div>
				</div>
			';
			$query = "SELECT hacker.id, clan_id, jailed_bail, jailed_till, jailed_reason FROM hacker WHERE jailed_from <= '$now' AND jailed_till >= '$now'";
			$result = mysqli_query($link, $query);
			if (mysqli_num_rows($result) == 0) echo '<div class="row">'.PrintMessage("Info", "No hackers in jail currently.").'</div>';
			else {
				echo '<div class="dark-bg">';
				while ($row = mysqli_fetch_assoc($result)) {
					echo '
						<div class="row hr-light">
							<div class="col w20">'.ShowHackerAlias($row['id'], $row['clan_id']).'</div>
							<div class="col w30">'; 
							if ($row['jailed_bail'] != 0) echo $row['jailed_reason'];
							else echo '<font color="red">Jailed by Staff</font>';

					echo '
							</div>		
							<div class="col w20">'.Seconds2Time(SecondsDiff($now,$row['jailed_till'])).'</div>
							<div class="col w10">'.$currency.number_format($row['jailed_bail']).'</div>
							<div class="col w20">';

							// can we bail out others?
							if ($row['jailed_bail'] != 0) {
								if (!IsJailed($hackerdata['id']) && !InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) {
									echo '
									<form method="POST" action="index.php" class="alt-design">
										<input type="hidden" name="h" value="dojail">
										<input type="hidden" name="action" value="bail">
										<input type="hidden" name="id" value="'.$row['id'].'">
										<input type="submit" value="Bail out!">
									</form>
									<form method="POST" action="index.php" class="alt-design">
										<input type="hidden" name="h" value="dojail">
										<input type="hidden" name="action" value="bust">
										<input type="hidden" name="id" value="'.$row['id'].'">
										<input type="submit" value="Bust out!">
									</form>';
								}
								else echo '&nbsp;';
							}
							else echo '---';

					echo '		
							</div>
						</div>';
				}
				echo '</div></div>';
			}
			
		echo '
		<br><br>';	
		
		// list the 10 most recent jailtimes
		echo '
			<h2>Recently in Jail</h2>
				<div class="row th light-bg">
					<div class="col w50">Hacker</div>
					<div class="col w50">Released on</div>
				</div>
				<div class="dark-bg">';
		$query = "SELECT id, clan_id, jailed_till FROM hacker WHERE jailed_till < ".$now." AND jailed_till <> 0 ORDER by jailed_till DESC LIMIT 10";
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) echo '<tr><td colspan="2">No recent jailbirds found..</td></tr>';
		while ($row = mysqli_fetch_assoc($result)) {
			echo '
				<div class="row hr-light">
					<div class="col w50">'.ShowHackerAlias($row['id'], $row['clan_id']).'</div>
					<div class="col w50">'.Number2Date($row['jailed_till']).'</div>
				</div>';
		}
		echo "</div>";
	}
?>
