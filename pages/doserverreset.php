<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['doserverhack'] != 1) return "Session error";
	$_SESSION['doserverhack'] = 0;
	
	// old password
	$old_password = "";
	if (!empty($_POST['old_password'])) $old_password = sql($_POST['old_password']);
	
	// new password
	$password = "";
	if (!empty($_POST['new_password'])) $password = sql($_POST['new_password']);
	
	if (!isvalidpassword($password)) return "The new password contains illegal characters. Alpha Numeric passwords only!";
	if (strlen($password) < 3 || strlen($password) > 20) return "Password length is incorrect. Minimal 3 characters, maximum of 20 characters.";

	// which server?
	$server_ip = "";
	if (!empty($_POST['server_ip'])) $server_ip = sql($_POST['server_ip']);
	
	// misc server hack checks
	$product_id = 0;
	$checkscope = false; // set it explicitly here, so that _inc_serverhackchecks leaves it at false
	$return_value = include_once("./pages/_inc_serverhackchecks.php");
	if ($return_value != 1) return $return_value;
	
	// are you allowed to own another server?
	if (NumServers($hackerdata['id']) >= MaxServers($hackerdata['id'])) return "You currently can not maintain more then ".MaxServers($hackerdata['id'])." servers.";
	
	// is this your server?
	if ($hacker_id == $hackerdata['id']) return "You can not reset your own server this way. If you want to reset a password for your own server, use the server manager instead.";

	// is your own gateway online? no gateway, no server attacks
	if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "Your clans' gateway is offline!";
	
	// still on n00bnet?
	if ($hackerdata['network_id'] != 2) return "You are not connected to the internet.";
	
    if (!CorrectFormHash("srv2", $_POST[$_SESSION['srv2_field']])) return "Wrong hash!";
    
	// are you allowed to change?
	if ($server_passdate > $now) return "You have to wait ".SecondsDiff($now,$server_passdate)." seconds before you can change this servers password";

	// RESET A PASSWORD
	$from = 0; // from now :)

	// check if old the password is ok
	if ($server_password != $old_password) {
		// you can't retry directly, to prevent bruteforce
		$pass_date = date($date_format, strtotime($server_passfailed_interval));
		$result = mysqli_query($link, "UPDATE server SET pass_date = '".$pass_date."' WHERE id = ".$server_id);
		// log the failed attempt
		AddLog ($server_od, "server", "", "Warning: Hack Attempt, Server: ".$server_name.", Logged: {$hackerdata['ip']}", $now);
		PrintMessage ("Error", "Wrong password. Access denied on server ".$server_name, "40%");
	}
	else {
		// get a list of all infections on this server
		$delete_infection = mysqli_query ($link, "SELECT infection.id, infection.hacker_id FROM infection WHERE infection.victim_entity = 'server' AND infection.victim_id = $server_id");
		$infection_id = array();
		while($infection_row = mysqli_fetch_assoc($delete_infection)) {
			// find out if the infection is owned by you or your clan
			$infection_clanid = mysqli_get_value("clan_id", "hacker", "id", $infection_row['hacker_id']); // which clan owns this infection?
			if ($infection_clanid == $hackerdata['clan_id']) $infection_id[] = $infection_row['id']; // if the server is owned by you or a clan mate, add it to the delete list
		}
		
		// remove clan infections from this server
		foreach ($infection_id as $clean_id)
			CleanInfection ($clean_id, "Connection lost");
		
		// set the next drop date
		$drop_date = date($date_format, strtotime("+".$server_drop_interval." minutes"));
		$result = mysqli_query($link, "UPDATE server SET drop_date = '".$drop_date."' WHERE id = ".$server_id);
		
		// resetting the password will make the server yours immediately
		$result = mysqli_query($link, "UPDATE server SET hacker_id = ".$hackerdata['id'].", password = '".$password."', previous_ownerid = '".$row['hacker_id']."', profit = 0 WHERE id = ".$server_id);
		
		AddLog ($server_id, "server", "", "Ownership of server transfered. ".$server_name, $now);
		PrintMessage ("Success", "You successfully connected to ".$server_name." and reset the password to your own. This is now your server.", "40%");
	}
?>