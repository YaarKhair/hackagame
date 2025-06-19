<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$ip = GetUserIP();
	if (!IsValidIP($ip)) {
		return "There was a problem verifying your identiy.";
	}
	
	// alias
	$alias = "";
	if (!empty($_POST['alias'])) $alias = sql($_POST['alias']); 
	
	
	if (!IsWhiteListed($ip) && IsMultipleAccount($ip)) return "There are already accounts registered from this IP ($ip). Only $accounts_per_ip accounts per IP are allowed.";
	
	// banned or blacklisted?
	if (IsBannedIP($ip)) return "This IP ($ip) is on our blacklist.";

	// email
	$email = "";
	if (!empty($_POST['email'])) $email = sql($_POST['email']);
	if (!isvalidemail($email)) return "Invalid email address.";

	if (!isvaliduser($alias))  		return "Alias contains illegal characters. Allowed is: ".$allowedcharsuser; 
	if (strlen($alias) < 3)  		return "Alias is too short. Minimum of 3 characters."; 
	if (strlen($alias) > 15)  		return "Alias is too long. Maximum of 15 characters."; 
	if (IsBannedAlias($alias))		return "Username contains banned words";

	// check alias	
	$result = mysqli_query($link, "SELECT last_click, email, banned_date FROM hacker WHERE alias = '$alias'"); // LEFT( email, 2 ) != '**' AND
	if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc ($result);
		$available_date = date($date_format, strtotime("-".$alias_available_months." months"));
		if ($row['last_click'] < $available_date || substr($row['email'], 0, 2) == "**" || $row['banned_date'] > 0)  {
			// rename the original alias owner
			$result = mysqli_query ($link, "UPDATE hacker SET prev_alias = alias, alias = concat(alias, '1') WHERE id = {$row['id']}");
			AddLog ($row['id'], "hacker", "staff", "Alias hijacked due to inactivity ($alias_available_months months)", $now);
		}
		else return "This alias is taken.";
	}
	
	$result = mysqli_query($link, "SELECT id FROM hacker WHERE email = '".$email."'");
	if (mysqli_num_rows($result) > 0) return "A hacker already exists with that email address.";
	
	// password, updated after the loss of the sourcecode and db on 25th of july 2014
	$pass1 = "";
	$pass2 = "";
	if (!empty($_POST['pass1'])) $pass1 = $_POST['pass1']; 
	if (!empty($_POST['pass2'])) $pass2 = $_POST['pass2'];
	if ($pass1 != $pass2) return "The passwords do not match.";
	if (!isvalidUserpassword($pass1)) return "Your password does not meet the minimum requirements. It must be at least 8 characters long, have both UPPERCASE and lowercase and at least one none alphanumeric value (like: $, %, # etc..).";
	
	// create a secure salted password
	$salt = generatePassword($salt_length);
	$salted_pass = SHA1($password_key.$pass1.$salt);
	
	// check if there was an referral id associated and check if it exists
	$referral_id = 0;
	$referral_code = 0;
	if(!empty($_SESSION['referral_code'])) {
		$referral_code = sql($_SESSION['referral_code']);
		$referral_result = mysqli_query($link, "SELECT id FROM hacker WHERE SHA1(started) = '$referral_code' AND npc = 0");
		if(mysqli_num_rows($referral_result) > 0) {
			$referral_row = mysqli_fetch_assoc($referral_result);
			$referral_id = $referral_row['id'];
		}
		// Check if the IP of the referral matches the registerer's IP
		if(mysqli_get_value("real_ip", "hacker", "id", $referral_id) == $ip) return "You signed up using a referral code, but the referer uses the same connection as you. Account creation aborted.";
	}
	
		
	// activation code
	$code = sha1(mt_rand(10000,99999));
	
	// country
	$country = "";
	if (!empty($_POST['country'])) {
		$country = sql($_POST['country']);
		if (strlen($country) != 2) return "Invalid country.";
		$result2 = mysqli_query ($link, "SELECT id FROM country WHERE code = '$country'");
		if (mysqli_num_rows($result2) == 0) return "Invalid country.";
	}
	
	$hacker_id = mysqli_next_id("hacker");
	// x days free premium
    $premium_date = date($date_format, strtotime("+$free_premium_days days"));

	$result = mysqli_query($link, "INSERT INTO hacker (alias, email, password, salt, activationcode, started, last_click, ip, real_ip, country, fbi_serverip, fbi_serverpass, donator_till, topics, support_tickets, referral_id, bytes) VALUES ('$alias', '$email', '$salted_pass', '$salt', '$code', '$now', '$now', '".randomip()."', '$ip', '$country', '".randomip()."', '".createrandomPassword()."', '$premium_date', $daily_topics, $daily_tickets, $referral_id, 10)");
	
	// give them the smallest HDD
	$result = mysqli_query($link, "SELECT id FROM product WHERE code = 'HDD' ORDER BY size ASC LIMIT 1");
	$row = mysqli_fetch_assoc($result);
	$result = mysqli_query($link, "INSERT INTO system (hacker_id, product_id, efficiency) VALUES (".$hacker_id.", ".$row['id'].", 100)");
	
	// send them some cash
	BankTransfer ($hacker_id, "hacker", $startmoney, "Some cash to get you started on n00bNET.");

	// Send an IM to their referer if they register
	if($referral_id > 0) SendIM(0, $referral_id, "Referral registeration", "$alias has accepted your invite and joined the game! Help him reach level $referral_level so you can get your reward!", $now);
	
	// activation mail
	$mail_body = "Hi, ".$alias.".<br><br>Thank you for signing up an account on HF.<br><br>To activate your HF game account use this link:<br><a href=\"".$gameurl."/guest.php?h=activate&id=".$hacker_id."&code=".$code."\">".$gameurl."/guest.php?h=activate&id=".$hacker_id."&code=".$code."</a><br><br>If the link is not clickable, then please copy/paste the link below to your browser:<br>".$gameurl."/guest.php?h=activate&id=".$hacker_id."&code=".$code; //mail body
	$subject = "Activate your account"; //subject
	$mailresult = SendMail($email, $subject, $mail_body); //mail command :)
	
	// sending mail failed! lets activate them instantly
	if ($mailresult != 1) {
		$msg = "You can now login and play!";
		PlayerTutorial($hacker_id, 0); // give them the first email with some general info
		$result = mysqli_query($link, "UPDATE hacker SET activationcode = '' WHERE id = $hacker_id");
	}	
	else $msg = "<br>Check your email (including your *SPAM* folder) to activate your account.";
	
	// whitelist alert to GA
	if (IsWhiteListed($ip)) {
		$result2 = mysqli_query ($link, "SELECT id FROM hacker WHERE clan_id = $staff_clanid");
		while ($row2 = mysqli_fetch_assoc($result2))
			SendIM(0, $row2['id'], "whitelist alert", "a user just registered, using the whitelisted IP $ip: [[@$alias]]", $now);
	}
	

    AddLog ($hacker_id, "hacker", "staff", "Account created from ip $ip", $now);
	PrintMessage ("success", "Account created. $msg", "40%");
?>