<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php	
	if (!empty($_POST['email'])) {
		$page2load = "h=welcome";
		$email = sql($_POST['email']);
		$_SESSION['email'] = $email;
		$password = $_POST['pass'];
		
		// lets see if there is a user with this data present in the database
		$query = "SELECT email, salt, activationcode, id, banned_date, banned_reason, password, alias, hybernate_till, failed_logins, lockout_till, lockout_reason, clan_id, real_ip FROM `hacker` WHERE `email` = '$email'";
		$result = mysqli_query($link, $query);
		$num_rows = mysqli_num_rows($result);
		if (mysqli_num_rows($result) == 0) { 
			 return "Wrong email address or password or your account is possibly disabled. Please try again.";
		}
		else {
			$row = mysqli_fetch_assoc($result);	
			if ($row['lockout_till'] > $now) 
				return "Your account has been locked out. Reason: {$row['lockout_reason']}<br><br>Your account will automatically be unlocked after ".Seconds2Time(SecondsDiff($now,$row['lockout_till']));
			
			// not yet activated? warn them! 
			if ($row['activationcode'] != "" || $row['activationcode'] != NULL) 
				Return "You need to activate your account by clicking the activation link in the email that was sent to you. If you did not receive this email, be sure to check your SPAM folder.<br><br>If you are still having issues, contact ".$gameemail; 
						
			// the password was reset by the admin
			if ($row['password'] == "" || $row['salt'] == "") {
				// Generate a random password and email it to them
				$newPassword = generatePassword($random_password_length);
				$salt = generatePassword($salt_length);
				$salted_password = SHA1($password_key.$newPassword.$salt);
				$update_result = mysqli_query($link, "UPDATE hacker SET password = '$salted_password', salt = '$salt' WHERE id = {$row['id']}");
				
				// Log it
				AddLog("hacker", $row['id'], "staff", "Auto Password reset for {$row['alias']} by ".GetUserIP(), $now); 
				
				// Prepare the message
				$to = $row['email'];
				$subject = 'Password reset';
				$message = "Dear {$row['alias']},<br><br>Your password was changed due to safety reasons.<br>Your new password is $newPassword<br><br>Game Administration";
				SendMail($to, $subject, $message);
				return "For safety reasons, a new password was sent to your email. If you did not receive this email, be sure to check your SPAM folder.";
			}
			if ($row['password'] != sha1($password_key.$password.$row['salt'])) {
				// bruteforce protection
				$result2 = mysqli_query($link, "UPDATE hacker SET failed_logins = failed_logins +1 WHERE id = ".$row['id']);
				if ($row['failed_logins'] +1 == $bruteforce_limit) {
					AddLog ($row['id'], "hacker", "staff", "Entered wrong password $bruteforce_limit times, account locked for $faultylogin_interval minutes [".GetUserIP()."]", $now);
					$lockout_till = date($date_format, strtotime("+".$faultylogin_interval." minutes"));
					$result2 = mysqli_query($link, "UPDATE hacker SET lockout_till = '$lockout_till', lockout_reason = 'Too many failed login attempts.' WHERE id = ".$row['id']);
				}	
				return "Wrong email address or password. Please try again.";
			}	
			if (IsHybernated($row['id'])) {
				include("./pages/hibernation.php");
				exit;
			}	
				
			if ($row['banned_date'] > 0)
				$page2load = "h=banned"; // redirect to banned message, which contains a link to the support ticket system

			// the ip is not correct, we want to let through the banned ip's because that account will then be caught by the auto-ban system.
			if (!IsValidIP(GetUserIP()) && !InGroup($row['id'], 1)) 
				Return "There was a problem verifying your identity.";

			// duplicate finder
			if (!IsWhiteListed($row['real_ip'])) {
				$score = 0;
				$log = false;
				$result2 = mysqli_query($link, "SELECT id, alias, last_login, offline_from, last_click FROM hacker WHERE id <> {$row['id']} AND real_ip = '{$row['real_ip']}'");
				if (mysqli_num_rows($result2) > 0) {
					$row2 = mysqli_fetch_assoc($result2);
					
					// close logins
					if ($row2['last_login'] > date($date_format, strtotime("-15 minutes"))) {
						$score = 2;
						$field = 'last_login';
						$message = "duplicate: {$row2['alias']} logged in [TIME] [TYPE] ago on same IP [$score]";
						$log = true;
					}	
					// offline on one , signin on other
					if ($row2['offline_from'] > date($date_format, strtotime("-15 minutes"))) {
						$score = 4;
						$message = "duplicate: {$row2['alias']} was offlined [TIME] [TYPE] ago on same IP [$score]";
						$field = 'offline_from';
						$log = true;
					}	
					// offline on one , signin on other
					if ($row2['last_click'] > date($date_format, strtotime("-15 minutes"))) {
						$score = 1;
						$message = "duplicate: {$row2['alias']} was active [TIME] [TYPE] ago on same IP [$score]";
						$field = 'last_click';
						$log = true;
					}	
					
					if($log == true) {
						// Find out the time and type
						$time = (strtotime($now) - strtotime($row2[$field])) / 60;
						$type = 'minutes';
						if(floor($time) == 0) {	// If the first character of time is 0 (meaning it's closer to seconds than minutes, use seconds)
							$time = strtotime($now) - strtotime($row2[$field]);
							$type = 'seconds';
						}
						$time = round($time);	// rounding is done after to make sure the seconds check goes correctly

						// Replace the time and type in the message
						$message = str_replace("[TIME]", $time, $message);
						$message = str_replace("[TYPE]", $type, $message);

						// Log it and increase the score
						AddLog($row['id'], "hacker", "staff", $message, $now);
						$result2 = mysqli_query($link, "UPDATE hacker SET duplicate_score = duplicate_score + $score WHERE id = ".$row['id']);	
					}
				}
			}
			
			// Are you using a tor IP?
			if(isTOR(GetUserIP())) AddLog($row['id'], "hacker", "abuse", "Using a TOR Node (".GetUserIP().")", $now);
			
			// all is well.. login
			$result2 = mysqli_query($link, "UPDATE hacker SET active = 1, last_login = $now, failed_logins = 0 WHERE id = ".$row['id']); // to detect inactive users
			if (IsFounder($row['id'])) $result2 = mysqli_query($link, "UPDATE clan SET last_login = $now WHERE id = ".$row['clan_id']); // to detect inactive clans
			$_SESSION['hacker_id'] = $row['id'];
			$_SESSION['sesdat01'] = sha1(GetUserIP());
			$_SESSION['sesdat02'] = sha1($row['password']);
			$_SESSION['sesdat03'] = sha1($row['alias']);
			$_SESSION['banklogin'] = "1"; // just so they are set, but with wrong data
			$_SESSION['clanbanklogin'] = "1"; // just so they are set, but with wrong data
			$_SESSION['helpscreen'] = "show";
			$_SESSION['hijacking'] = true;
			if ($row['banned_date'] == 0 && !InGroup($row['id'], 1)) LogUserIP($row['id']); // if you are not currently banned we log your ip.
			if (!InGroup($row['id'], 1)) AddLog ($row['id'], "hacker", "staff", "logged in from IP ".GetUserIP(), $now);
			AddLog ($row['id'], "hacker", "interval", "login", $now); // bot detection
			if ($page2load == "") $page2load = $_SESSION['redir'];
			echo '<script type="text/javascript">location.href = "/?'.$page2load.'"</script>';
		}	
	}
	else return "No email address specified.";
?>