<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['doedithacker'] != 1) return "Session error";
	$_SESSION['doedithacker'] = 0;
		
	// password
	$pass0 = "";
	$pass1 = "";
	$pass2 = "";
	if (!empty($_POST['pass0'])) $pass0 = $_POST['pass0'];
	if (!empty($_POST['pass1'])) $pass1 = $_POST['pass1'];
	if (!empty($_POST['pass2'])) $pass2 = $_POST['pass2'];
	if ($pass1 != "") {
		if ($pass1 != $pass2) return "The two new passwords do not match.";
		if(!isValidUserPassword($pass1)) return "Password needs to be at least 8 characters consiting of at least an uppercase, a lowercase and a symbol.";
	}	
	
	// check if the old password is correct.
	if ($hackerdata['password'] != sha1($password_key."$pass0".$hackerdata['salt'])) return "The old password is incorrect.";
	
	// chat invisibility
	$invisible = 0;
	if (!empty($_POST['invisible'])) $invisible = checkbox($_POST['invisible']); 

	$alias = $hackerdata['alias'];
	$color = $hackerdata['chat_color'];
	$chat_alert = 0;
	$publicstats = 0;
	$show_friends = 0;
	$show_foes = 0;
	$sound_email = 0;
	$custom_css = "";
	$custom_logo = "";
	$show_ads = 0;
	$show_epskill = 0;
	$show_tutorial = 0;
	$show_tooltips = 0;
	$cc2mail = 0;
	
	if ($hackerdata['donator_till'] > $now) {
		if (!empty($_POST['alias']) && $_POST['alias'] != $hackerdata['alias']) {
			if ($hackerdata['nextalias_date'] > $now) return "You can change your alias again at ". Number2Date($hackerdata['nextalias_date']);
			$alias = sql($_POST['alias']); 
			// check alias
			if (!isvaliduser($alias))  		return "Alias contains illegal characters. Allowed is: ".$allowedcharsuser; 
			if (strlen($alias) < 3)  		return "Alias is too short. Minimum of 3 characters."; 
			if (strlen($alias) > 15)  		return "Alias is too long. Maximum of 15 characters."; 
			if (IsBannedAlias($alias))		return "Username contains banned words";
			
			// check alias	
			$result = mysqli_query($link, "SELECT last_click, email, banned_date FROM hacker WHERE alias = '$alias'");
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc ($result);
				$available_date = date($date_format, strtotime("-".$alias_available_months." months"));
				if ($row['last_click'] < $available_date || substr($row['email'], 0, 2) == "**" || $row['banned_date'] > 0) {
					// rename the original alias owner
					$result = mysqli_query ($link, "UPDATE hacker SET prev_alias = alias, alias = concat(alias, '1') WHERE alias = '$alias'");
					AddLog ($row['id'], "hacker", "staff", "Alias hijacked (inactive, disabled or banned)", $now);
				}
				else return "This alias is taken.";
			}
		}	
		
		// color
		if (!empty($_POST['color']) && $_POST['color'] != $color)  {
			$color = sql($_POST['color']); 
			if (strlen($color) != 6)  return "A clan html color is always 6 characters: <strong>C8C8C8</strong>."; 
			if (!isHex($color))  return "Invalid clan color."; 
			if ($color == "000000") return "BLACK is NOT allowed!";
		}	
		
		// premium stuff
		if (!empty($_POST['show_ads'])) $show_ads = checkbox($_POST['show_ads']); 
		if (!empty($_POST['sound_email'])) $sound_email = checkbox($_POST['sound_email']); 
		if (!empty($_POST['publicstats'])) $publicstats = checkbox($_POST['publicstats']); 
		if (!empty($_POST['show_friends'])) $show_friends = checkbox($_POST['show_friends']); 
		if (!empty($_POST['show_foes'])) $show_foes = checkbox($_POST['show_foes']); 
		if (!empty($_POST['show_epskill'])) $show_epskill = checkbox($_POST['show_epskill']); 
		if (!empty($_POST['cc2mail'])) $cc2mail = checkbox($_POST['cc2mail']); 
		if (!empty($_POST['custom_css'])) {
			$custom_css = sql($_POST['custom_css']); 
			if ($custom_css == "default") $custom_css = ""; // no custom css
		}	
		if (!empty($_POST['custom_logo'])) {
			$custom_logo = sql($_POST['custom_logo']); 
			if ($custom_logo == "hflogo1") $custom_logo = ""; // no custom css
		}	
	}
	
	// avatar	// Note: this also handles removing avatars
	$entity = "hacker";
	$entity_id = $hackerdata['id'];
	$return = include ("./pages/_inc_uploadavatar.php");
	if($return != 1) return $return;

	// hybernation
	$hybernate = 0;
	if (!empty($_POST['hybernate'])) {
		$hybernate = intval($_POST['hybernate']); 
		if ($hybernate < 7 || $hybernate > 21) return "You can only hibernate between 7 and 21 days";
		if ($now < $hackerdata['nexthibernate_date']) {
			$seconds = SecondsDiff($now, $hackerdata['nexthibernate_date']);
			return "You were recently in hibernation. You can go back into hibernation in ".Seconds2Time($seconds);
		}	
		if ($hackerdata['clan_id'] != 0) {
			if (IsFounder($hackerdata['id'])) return "You can not hibernate while you are founder. Pass your role to another clan member and try again.";
			// hibernating while holding KOTR servers? lets fix that.
			//$kotr_servers = mysqli_query($link, "SELECT id FROM server WHERE id IN (".implode(",", $kotr['all_servers']).") AND hacker_id = {$hackerdata['id']}");
			//while($kotr_row = mysqli_fetch_assoc($kotr_servers)) restoreKOTRserver($kotr_row['id']);
		}
	}
	
	// tutorial messages
	if(isset($_POST['show_tutorial'])) $show_tutorial = checkbox($_POST['show_tutorial']);
	$result = mysqli_query($link, "UPDATE hacker SET show_tutorial = $show_tutorial WHERE id = ".$hackerdata['id']);
	// tooltips
	if(isset($_POST['show_tooltips'])) $show_tooltips = checkbox($_POST['show_tooltips']);
	$result = mysqli_query($link, "UPDATE hacker SET show_tooltips = $show_tooltips WHERE id = ".$hackerdata['id']);
	// chat alert
	if(isset($_POST['chat_alert'])) $chat_alert = checkbox($_POST['chat_alert']);
	$result = mysqli_query($link, "UPDATE hacker SET chat_alert = $chat_alert WHERE id = ".$hackerdata['id']);


	// country
	$country = "";
	if (!empty($_POST['country'])) {
		$country = sql($_POST['country']);
		if (strlen($country) != 2) return "Invalid country.";
		$result2 = mysqli_query ($link, "SELECT id FROM country WHERE code = '$country'");
		if (mysqli_num_rows($result2) == 0) return "Invalid country.";
		$result = mysqli_query($link, "UPDATE hacker SET country = '".$country."' WHERE id = ".$hackerdata['id']);
	}
	
	// extra info
	$extra_info = "";
	if (!empty($_POST['extra_info'])) {
		$extra_info = $_POST['extra_info'];
		$extra_info = preg_replace('#\r?\n#', '[br]', $extra_info);
		$extra_info = sql($extra_info, false);
		$extra_info = str_replace("[br]", "<br>", $extra_info);
		$extra_info = FilterTags ($extra_info, $hackerdata['id']);
	}
	$result = mysqli_query($link, "UPDATE hacker SET extra_info = '".$extra_info."' WHERE id = ".$hackerdata['id']); // always update the extra info, because else you would be never be able to clear it.

	if ($pass1 != "") {
		$salt = generatePassword($salt_length);
		$salted_pass = sha1($password_key.$pass1.$salt);
		$result = mysqli_query($link, "UPDATE hacker SET password = '$salted_pass', salt = '$salt' WHERE id = ".$hackerdata['id']);
	}	
	
	if ($hybernate > 0) {
		$from = $now;
		$till = date($date_format, strtotime("+".$hybernate." days"));
		$nexthibernate_date = date($date_format, strtotime("+".$hybernate+$hibernation_interval." days"));
		$result = mysqli_query($link, "UPDATE hacker SET nexthibernate_date = '".$nexthibernate_date."', hybernate_from = ".$from.", hybernate_till = ".$till." WHERE id = ".$hackerdata['id']);
		AddLog ($hackerdata['id'], "hacker", "hibernation", "Went into hibernation until ".Number2Date($till), $now);
	}
	// donators
	if ($hackerdata['donator_till'] > $now) {
		if ($alias != $hackerdata['alias']) {
			// announce it on facebook
			$message = "The hacker previously known as {$hackerdata['alias']} has changed his/her alias to $alias.";
			SendMail ("trigger@recipe.ifttt.com", "#update", $message);
			// update next update date
			$nextalias_date = date($date_format, strtotime("+".$premium_hacker_changeinterval." months"));
			$result = mysqli_query($link, "UPDATE hacker SET nextalias_date = '$nextalias_date', prev_alias = '{$hackerdata['alias']}', alias = '$alias' WHERE id = {$hackerdata['id']}");
			// Log it
			AddLog($hackerdata['id'], "hacker", "staff", "Alias change: From [{$hackerdata['alias']}] to [$alias]", $now);
		}	
		$result = mysqli_query($link, "UPDATE hacker SET cc2mail = $cc2mail, show_ads = $show_ads, custom_logo = '$custom_logo', custom_css = '$custom_css', chat_color = '$color', publicstats = $publicstats, show_friends = $show_friends, show_foes = $show_foes, show_epskill = $show_epskill, sound_email = $sound_email WHERE id = ".$hackerdata['id']);
	}
	
	$forced = 0;
	if(!empty($_POST['forced'])) $forced = intval($_POST['forced']);
	
	if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) $result = mysqli_query($link, "UPDATE hacker SET invisible = $invisible WHERE id = ".$hackerdata['id']);
	if ($hackerdata['force_passchange'] == 1 && $forced == 1) $result = mysqli_query($link, "UPDATE hacker SET force_passchange = 0 WHERE id = ".$hackerdata['id']);			
	PrintMessage ("Success", "Account updated.", "40%");
	include ("pages/profile.php");
?>