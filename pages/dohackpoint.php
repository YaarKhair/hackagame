<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!empty($_POST['action'])) $action = $_POST['action'];
	else return "An error occurred executing this page.";
	
	if ($action == "end_contract") {
		// costs 1HP, unless you're on noobnet
		if ($hackerdata['network_id'] == 1) $points = 0;
		else $points = $end_contract_hp;
		
		if ($hackerdata['hackpoints_credit'] < $points) return "You do not have the required $points hackpoints.";
		
		$result = mysqli_query ($link, "UPDATE hacker set nextnpc_date = '$now' WHERE id = {$hackerdata['id']}");
		$result = mysqli_query ($link, "UPDATE npc_mission SET date = '$now' WHERE hacker_id = {$hackerdata['id']}");

		AddHackpoint ($hackerdata['id'], 0, $points * -1, "Ended a contract");
		PrintMessage ("Success", "The contract timer was reset. You will be able to start as soon as you get the results from the current mission.");
	}
	
	if($action == "increase_contract_time") {
		$points = 0;
		if(!empty($_POST['hackpoint'])) $points = intval($_POST['hackpoint']);
		if($points <= 0) return "Invalid hackpoint amount.";
		if ($hackerdata['hackpoints_credit'] < $points) return "You do not have the required $points hackpoints.";
		// Make out the time and increase it
		$time = $points * $hpc2contract_time;
		$contract_time = mysqli_get_value("date", "npc_mission", "hacker_id", $hackerdata['id']);
		$contract_time = date($date_format, strtotime("$contract_time + $time minutes"));
		$result = mysqli_query($link, "UPDATE npc_mission SET date = '$contract_time' WHERE hacker_id = {$hackerdata['id']}");
		$result = mysqli_query($link, "UPDATE hacker SET nextnpc_date = '$contract_time' WHERE id = {$hackerdata['id']}");
		
		// Take out the HPCs
		AddHackPoint($hackerdata['id'], 0, $points * -1, "Increase contract timer.");
		PrintMessage("Success", "You have successfully added $time minutes to your ongoing contract.");
	}
?>