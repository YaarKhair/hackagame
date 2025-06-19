<?php
	include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	include("/var/www/modules/functions.php");
	
	// this game has fixed moments to mail inactive players
	// 4 months inactive, 8 months inactive, 1 year inactive
	
	for ($i = 0; $i < 3; $i++) {
		if ($i == 0) { $months = 24; $mails_sent = 4; }
		if ($i == 1) { $months = 12; $mails_sent = 3; }
		if ($i == 2) { $months = 8; $mails_sent = 2; }
		if ($i == 3) { $months = 4; $mails_sent = 1; }
		
		$last_click = date($date_format, strtotime("-".$months." months"));
		$mail_body = "Hi Hacker,<br><br>It's been $months months since we last saw you on HackerForever.<br><br>We would love to see you return. Please login with your old account, or start fresh with a new one ($startmoney start cash!).<br><br>Hope to see you ingame at <a href=\"http://www.hackerforever.com\">http://www.hackerforever.com</a><br><br>Best regards,<br>HF Game Administration";
		$result = mysqli_query ($link, "SELECT email, alias FROM hacker WHERE banned_date = 0 AND mails_sent < $mails_sent AND last_click < '$last_click'");
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$subject = $row['alias'].", where are you?";
				$email = $row['email'];
				// disabled accounts start with **DISABLED**, if it's not disabled, mail them
				if (substr($email, 0, 2) != "**") $mailresult = SendMail($email, $subject, $mail_body); //mail command
			}
		}
		// update so they don't get the same mail twice.
		$result = mysqli_query ($link, "UPDATE hacker SET mails_sent = $mails_sent WHERE banned_date = 0 AND mails_sent < $mails_sent AND last_click < '$last_click'");
	}
	AddLog (0, "hacker", "cron", "cron_mail", $now);	
?>