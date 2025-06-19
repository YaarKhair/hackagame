<?php
	// this cron runs at noon
	include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	include("/var/www/modules/functions.php");
	include("/var/www/modules/cronstats.php");
	include("/var/www/modules/cronworldrankep.php");
	include("/var/www/modules/cronworldrankhp.php");
	
	// Cron that adds random anonymous bounties to promote PVP
	/*$yesterday = date($date_format, strtotime("- 1 day"));
	$ep = 35157;	// ep for level 81
	$hackers2add = mt_rand (0, 2); // 33% change we add a hacker
	if ($hackers2add == 2) {
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE last_click >= '$yesterday' AND ep >= $ep AND clan_id <> $staff_clanid AND npc = 0 AND banned_date = 0 AND clan_id != {$kotr['clan_id']} AND LEFT(email, 2) != '**' ORDER BY rand() LIMIT 1");
		$tools = array(16, 17, 36, 76, 77);	// list of tools: CD, Software Leecher, HDD Destroyer, Inbox Crusher, Message stealer
		while($row = mysqli_fetch_assoc($result)) {

			// Pick a tool
			shuffle($tools);
			$tool_id = $tools[0];
			$tool_price = mysqli_get_value("price", "product", "id", $tool_id);

			// Figure out the price
			$bounty = ($tool_price * mt_rand(2, 5)) + (10000 * mt_rand(5,20));
			$bounty = round($bounty, -3);

			// Pick a date
			$randoms = array("hours" => array("min" => 1, "max" => 16), "minutes" => array("min" => 1, "max" => 59), "seconds" => array("min" => 1, "max" => 59));
			$date = $now;
			foreach($randoms as $entity => $numbers) {
				$num = mt_rand($numbers['min'], $numbers['max']);
				$date = date($date_format, strtotime("$date + $num $entity"));	// + 5 hours, + 5 minutes, + 10 seconds... etcc
			}

			// Insert
			$insert = mysqli_query($link, "INSERT INTO bounty (contracter_id, victim_id, tool_id, date, anonymous, reward) VALUES ($ibot_id, {$row['id']}, $tool_id, '$date', 1, $bounty)");
		}
	}*/
	AddLog (0, "hacker", "cron", "cron_24h2", $now);	
?>