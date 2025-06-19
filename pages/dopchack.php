<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['dopchack'] != 1) return "Session error";
	$_SESSION['dopchack'] = 0;
    
    if (!CorrectFormHash("pc", $_POST[$_SESSION['pc_field']])) return "Wrong hash!";
    
	if (!empty($_POST['ip'])) $hacker_ip = sql($_POST['ip']);
	else return "An invalid ip was supplied.";
	if (!empty($_POST['product_id'])) $product_id = intval($_POST['product_id']);
	else return "An invalid method of attack was selected.";
	if (!empty($_POST['overclock'])) $overclock = CheckBox($_POST['overclock']);
	else $overclock = 0;
	
	// product checks
	if (!HasOnHDD($hackerdata['id'], $product_id)) return "You do not have this tool on your HDD.";
	else if (!AllowedUseProduct($hackerdata['id'], $product_id)) return "Your level does not permit you to use this tool.";
	// n00btool? only on n00bnet
	if ($product_id == 74 && $hackerdata['network_id'] != 1) return "This tool can only be used on n00bNET";
	
	if (mysqli_get_value("code", "product", "id", $product_id) != "PCHACK") return "Wrong tool for the job.";
	
	// misc pc hack checks
	$return_value = include_once("./pages/_inc_pchackchecks.php");
	if ($return_value != 1) return $return_value;
	
	
	// used this product, thus remove 1 from the inventory of the hacker
	DeleteFromInventory($hackerdata['id'], $product_id);
	
	/* CALCULATE CHANCE */
	// you can add extra chance to your hack by overclocking or perks
	$extra_chance = 0;

	// Overclock?
	if($overclock == true) {
		if($hackerdata['next_overclock'] > $now) return "You are only allowed to overclock once every 24 hours.";
		
		$extra_chance += $overclock_increase_chance;
		
		$pc_hack_interval += $overclock_increase_time_pc;
		$after1day = date($date_format, strtotime("+ $overclock_next_overclock_hours hours"));
		$overclock_update = mysqli_query($link, "UPDATE hacker SET next_overclock = '$after1day' WHERE id = {$hackerdata['id']}");
		
		// Now also increase his server timer
		$server_timer = $hackerdata['nextserverhack_date'];
		if($server_timer == 0 || $now > $server_timer) $server_timer = $now;
		$server_timer = date($date_format, strtotime("$server_timer + $overclock_increase_time_server minutes"));
		$server_timer_result = mysqli_query($link, "UPDATE hacker SET nextserverhack_date = '$server_timer' WHERE id = {$hackerdata['id']}");
	}

	// Perks go before execution
	$perk = GetPerkValue($hackerdata['id'], "PERK_PCHACK");
	$extra_chance += $perk;

	// FEED BATTLESYS ALL THESE NUMBERS
	$chance = BattleSysPvP ($hackerdata['id'], $hacker_id, $extra_chance);
	$target = Alias4Logs ($hacker_id, "hacker");
	PrintMessage ("Success", "Connecting to $target ... [OK]<br>Initiating ".mysqli_get_value("title", "product", "id", $product_id)." [OK]<br>Hack initiated. [OK]", "40%");

	// noobtool has 100% of landing
	if ($product_id == 74) {
		$chance = 100; 
		$hack_active = date($date_format, strtotime("+5 minutes")); // a quick reply
	}	
	/* END OF CHANCE */

	// calculate how long it will take
	$duration = $pc_hacktime - round(GetEfficiency ($hackerdata['id'], "INTERNET") / $internet_divider); 
	$hack_active = date($date_format, strtotime("+".$duration." minutes")); // when the hack result is active
			
	// calculate EP and Skill
	$ep_fail = round(GainEP($chance) / 10);
	$skill_fail = round(GainSkill($chance) / 10);
	
	// now set the next hack date, so we can't hack for xx minutes
	$next_hack = date($date_format, strtotime("+".$pc_hack_interval." minutes"));
	$result = mysqli_query($link, "UPDATE hacker SET nextpchack_date = '".$next_hack."' WHERE id = ".$hackerdata['id']);
	$_SESSION['nextpchack_date'] = $next_hack;

	// hack
	$product_title = mysqli_get_value("title", "product", "id", $product_id);
	AddLog ($hackerdata['id'], "hacker", "hack", "$product_title initiated on ".Alias4Logs($hacker_id, "hacker"), $now);
	
	// hack succeeded. huray!
	if (WillItWork($chance)) {
		$success = 1;
	}	
	else {
		$success = 0;
		// jailed when failed?
		if (GetJailed($hackerdata['id'], $hack_jailchance))
			Jail($hackerdata['id'], $hack_jailbail, $hack_jailtime, "You got caught trying to hack your way into a system and were sent to jail.");
	}
	// insert the hack into the crontable
	$result2 = mysqli_query($link, "INSERT INTO infection (hacker_id, victim_id, victim_entity, victim_ip, product_id, date, chance, success) VALUES ({$hackerdata['id']}, $hacker_id, 'hacker', '$hacker_ip', $product_id, '$hack_active', $chance, $success)");
	include ("./pages/infections.php");
?>