<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$_SESSION['npc_mission'] = 1; // anti refresh
	// noob cancel for free
	if ($hackerdata['network_id'] == 2)
		$hp_price = $end_contract_hp;
	else
		$hp_price = 0;
		
	// your timer is not ready yet
	if ($now <= $hackerdata['nextnpc_date']) {
		//$current_contract = mysqli_get_value_from_query("SELECT details FROM log WHERE event = 'contract' AND hacker_id = {$hackerdata['id']} AND date <= '$now' ORDER BY date DESC LIMIT 1", "details");
		echo '<div class="row mv10">
				<div class="col w50">
					<h2>End Contract</h2>
					<div class="light-bg">
						<p>You can force a contract to end immediately by spending HackPoints (HP).<br>The cost is '.$end_contract_hp.'HP. You currently have '.$hackerdata['hackpoints_credit'].'HP left to spend.<br><br>
						<form method="POST" action="index.php">
								<input type="hidden" name="h" value="dohackpoint">
								<input type="hidden" name="action" value="end_contract">
								<input type="submit" value="Force contract end ('.$hp_price.'HP)">
							</form>
						</p>
					</div>
				</div>
					
				<div class="col w50">
					<h2>Increase contract time</h2>
					<div class="light-bg">
						<p>You can increase your contract time by using your HPC points (1 points = 15 minutes)<br><br>
							<form method="POST" action="index.php">
								Amount of hackpoints to use: <input type="text" name="hackpoint">
								<input type="hidden" name="h" value="dohackpoint">
								<input type="hidden" name="action" value="increase_contract_time"><br>
								<input type="submit" value="Increase contract time">
							</form>
						</p>
					</div>
				</div>
			</div>';
			if(!empty($current_contract))
			echo '<div class="row mv10">
				<h2>Current Contract</h2>
				<div class="light-bg">
					<p>'.$current_contract.'</p>
				</div>
			</div>';	
			
		return "Please be patient. Your system is not yet ready for another contract.";
	}

	// a contract is still listed on your name, wait for it to finish
	$result = mysqli_query($link, "SELECT id FROM npc_mission WHERE hacker_id = {$hackerdata['id']}");
	//if (mysqli_num_rows($result) > 0) return "Please wait until your current contract is finished entirely (reward, email, etc)";
	
	// are you wanted by the FBI? we don't want you here
	if ($hackerdata['fbi_wanteddate'] > 0) return "You are wanted by the FBI. We don't need that kind of attention. Come back when you've sorted your business with the feds.";
?>
	<h1>NPC Mission</h1>
	<img src="images/theagency.png" align="right" />
	<p>We are The Agency, a Hacker Contracting Service. We bring together supply and demand.<br><br>
		We provide a unique service that guarantees the anonymousity of both you and our clients. We will act as the communication channel between you and the client. When you apply for a contract we will match you up with a client and arrange communications from there on.<br><br>
		If you got the skills, then we've got work for you.<br><br>
		<form method="POST" action="index.php" name="hf_form">
			<input type="hidden" name="h" value="donpcmission">
			<input type="submit" value="Request a Contract">
		</form>
	</p>
