<?php
	include ("modules/permissions.php");
	
	if ($_SESSION['dosendmoney'] != 1) return "Session error";
	$_SESSION['dosendmoney'] = 0;
	
	if (EP2Level(GetHackerEP($hackerdata['id'])) < $moneysend_level && !$is_staff) return "You can not send money until you reach level $moneysend_level , you can pay and get paid though.";
    
	$amount = 0;
	if (!empty($_POST['amount'])) $amount = intval($_POST['amount']);
	if ($amount < $moneysend_minimum) return ("The minimum amount you can transfer is ".$currency.$moneysend_minimum);
	$name = "";
	if (!empty($_POST['name'])) $name = sql($_POST['name']);
	
	$reason = "";
	if (!empty($_POST['reason'])) {
		$reason = sql($_POST['reason']);
		if (strlen($reason) > 50) $reason = substr($reason, 0, 50);
	}
	
	$from = "";
	if (!empty($_POST['from'])) $from = sql($_POST['from']);
		
	$to = "";
	if (!empty($_POST['to'])) $to = sql($_POST['to']);

	if($from != "hacker" && $from != "clan") return "Invalid account";
	if($to != "hacker" && $to != "clan") return "Invalid account";

	if ($from == "clan" && $hackerdata['clan_council'] != 1) return "You are not part of the clan council.";

	// 1 time login per session or when the password changes
	$requirelogin = false;
	if ($from == "hacker") {
		$pass2check = $hackerdata['password'];
		if ($_SESSION['banklogin'] != $pass2check) $requirelogin = true;
	}	
	else { 
		$pass2check = mysqli_get_value ("bankaccount_password", "clan", "id", $hackerdata['clan_id']);
		if ($_SESSION['clanbanklogin'] != $pass2check) $requirelogin = true;
	}
	if ($requirelogin) return "Your session expired!";

	
	if ($amount < 1 || $name == "") return "Transfer cancelled, no money sent.";
	if ($to == $from) {
		if ($from =="hacker" && strtolower($name) == strtolower($hackerdata['alias'])) return "Money loop error.";
		if ($from =="clan" && strtolower($name) == strtolower($hackerdata['clan_alias'])) return "Money loop error.";
	}	
	
	// receiver check
	if ($to == "hacker") $result = mysqli_query($link, "SELECT * FROM $to WHERE alias = '$name' AND banned_date = 0 AND active = 1");
	else $result = mysqli_query($link, "SELECT * FROM $to WHERE alias = '$name' AND active = 1");
	if (mysqli_num_rows($result) == 0) return "No bankaccount found for $to $name";
	else {
		$row = mysqli_fetch_assoc($result);
		if (!$is_staff) {
			if ($to == "hacker" && $row['id'] != $hackerdata['id'])
			{
				if ($row['real_ip'] == $hackerdata['real_ip']) // && !IsWhiteListed($row['real_ip']))
					Return "This function is unavailable for people sharing an IP.";
				if ($hackerdata['network_id'] != $row['network_id']) 
					Return "The person you're trying to send money to is connected to a different network.";
			}	
			if ($hackerdata['network_id'] == 1) return "You can not send money while connected to n00bNET.";
			if ($to == "clan" && !IsOnlineServer(GetGateway($row['id']))) return "Their gateway is currently offline.";
			if ($from == "clan" && !IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "Your gateway is currently offline.";
			if ($to == "hacker" && $row['hybernate_till'] > $now) return "You can not send money to someone who is hibernating.";
		}	
	}
	
	// sender check
	if ($from == "hacker") { $from_id = $hackerdata['id']; $by = ''; } else { $from_id = $hackerdata['clan_id']; $by = " by ".$hackerdata['alias']; }
	$result2 = mysqli_query($link, "SELECT * FROM $from WHERE id = $from_id AND bankaccount >= $amount");
	if (mysqli_num_rows($result2) == 0) return "You do not have ".$currency.number_format($amount)." on your $from bankaccount.";
	else $row2 = mysqli_fetch_assoc($result2);
	
	// extract from either hacker of clan account
	$reason_to = "Received from $from ".$row2['alias']." [{$hackerdata['ip']}], reason: $reason";
	$reason_from = "Sent to ".$row['alias'].$by.", reason: $reason";
	// substract from sender account
	BankTransfer ($from_id, $from, $amount*-1, $reason_from);
	// alert to sender
	PrintMessage ("Success", "The money (".$currency.number_format($amount).") was transferred successfully to the ".$to." ".$row['alias'], "40%"); // screen
	
	// now calculate interest
	if ($to == "hacker") $interest = $hacker_interest;
	else $interest = $clan_interest;
	$amount = round(($amount / 100) * (100 - $interest));
	BankTransfer ($row['id'], $to, $amount, $reason_to);

	// alert to reciever
	if ($from == "hacker") $wikilink = '@';
	else $wikilink = '#';
	if ($to == "hacker") SendIM (0, $row['id'], "Cyber Bank", "The ".$from." [[".$wikilink.$row2['alias']."]] has sent you ".$currency.number_format($amount)." from IP {$hackerdata['ip']}<br>Reason: ".$reason, $now);	// instant message
?>