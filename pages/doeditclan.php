<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['doeditclan'] != 1) return "Session error";
	$_SESSION['doeditclan'] = 0;
		
	// tag
	$shorttag = "";
	if (!empty($_POST['shorttag'])) { 
		$shorttag = sql($_POST['shorttag']); 
		if (strlen($shorttag) < 1) 			return "Short tag	is too short. Minimum of 1 characters.<br>";
		if (strlen($shorttag) > 3) 			return "Short tag is too long. Maximum of 3 characters.<br>";
		if (!isvalidclan($shorttag)) 			return "Short tag contains invalid characters. Allowed is: ".$allowedchars;
		if (IsBannedAlias($shorttag)) 	return "Clan shorttag contains banned words";
		
		$result = mysqli_query($link, "SELECT id FROM clan WHERE id <> ".$hackerdata['clan_id']." AND active = 1 AND shorttag = '".$shorttag."'");
		if (mysqli_num_rows($result) > 0) { return "A clan already exists with that short tag."; }
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

	// password
	$pass0 = "";
	$pass1 = "";
	$pass2 = "";
	if (!empty($_POST['pass0'])) $pass0 = sql($_POST['pass0']); 
	if (!empty($_POST['pass1'])) $pass1 = sql($_POST['pass1']); 
	if (!empty($_POST['pass2'])) $pass2 = sql($_POST['pass2']); 
	if ($pass1 != $pass2) return "The passwords do not match."; 
	if (!isvalidpassword($pass1) && $pass1 != "") return "Password can not be empty and only contain: a-z, A-Z, 0-9"; 
	
	// Hash pass
	$password_hashed = SHA1($pass1);
	
	// check if the old password is correct.
	$result = mysqli_query($link, "SELECT alias, bankaccount_password, salt, nextalias_date FROM clan WHERE id = {$hackerdata['clan_id']}");
	$row = mysqli_fetch_assoc($result);
	$oldalias = $row['alias'];
	$alias = $oldalias;
	
	// check old passowrd
	//echo $row['bankaccount_password'].'<br>'.sha1($pass0);
	if ($row['bankaccount_password'] != sha1($pass0)) return "You entered the incorrect current password.";

	
	// color
	$color = "";
	if (!empty($_POST['color']))  $color = sql($_POST['color']); 
	if (strlen($color) != 6)  return "A clan html color is always 6 characters: <strong>C8C8C8</strong>."; 
	if (!isHex($color))  return "Invalid html color."; 
	if ($color == "000000") return "BLACK is NOT allowed!";
	
	
	if ($hackerdata['donator_till'] > $now) {
		if (!empty($_POST['alias']) && $_POST['alias'] != $oldalias) {
			if ($row['nextalias_date'] > $now) return "You can change your clans' alias again at ". Number2Date($row['nextalias_date']);
			
			$alias = sql($_POST['alias']);
			$result = mysqli_query($link, "SELECT id FROM clan WHERE active = 1 AND alias = '$alias'");
			if (mysqli_num_rows($result) > 0) return "This alias is already taken."; 
			if (strlen($alias) < 3) 			return "Clanname is too short. Minimum of 3 characters.<br>";
			if (strlen($alias) > 20) 			return "Clanname is too long. Maximum of 20 characters.<br>";
			if (!isvalidclan($alias)) 			return "Clanname contains invalid characters. Allowed is: $allowedchars";
			if (IsBannedAlias($alias)) 	return "Clan alias contains banned words";
			AddLog($hackerdata['clan_id'], "clan", "staff", "Clan name change: From [{$row['alias']}] to [$alias]", $now);
		}
	}
	
	// avatar
	$entity = "clan";
	$entity_id = $hackerdata['clan_id'];
	$return = include ("./pages/_inc_uploadavatar.php");
	if($return != 1) return $return;
	
	// donators
	if ($hackerdata['donator_till'] > $now) {
		if ($alias != $oldalias) {
			// announce it on the frontpage.
			$result = mysqli_query($link, "SELECT id FROM topic WHERE title LIKE 'News' AND clan_id = 0 AND board_id = 0 AND post_id = 0");
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				$result = mysqli_query($link, "INSERT INTO topic (board_id, post_id, clan_id, hacker_id, date, title, message, lastpost_date, lastpost_hackerid) VALUES ({$row['id']}, 0, 0, $ibot_id, '$now', 'Name Change!', 'The clan previously known as $oldalias has changed their alias to [[#$alias]].<br><br>Please make note of this, because your logs might still display the old name.', '$now', $ibot_id)");
				// update last post data for current board
				$result = mysqli_query($link, "UPDATE topic SET lastpost_date = '$now', lastpost_hackerid = $ibot_id WHERE post_id = 0 AND board_id = 0 AND id = {$row['id']}");
			}	
			$nextalias_date = date($date_format, strtotime("+".$premium_clan_changeinterval." months"));
			$result = mysqli_query($link, "UPDATE clan SET nextalias_date = '$nextalias_date', prev_alias = '$oldalias', alias = '$alias' WHERE id = {$hackerdata['clan_id']}");
		}	
	}
	$result = mysqli_query($link, "UPDATE clan SET color = '".$color."', shorttag = '".$shorttag."' WHERE id = ".$hackerdata['clan_id']);
	if($extra_info != "") $update_info = mysqli_query($link, "UPDATE clan SET extra_info = '$extra_info' WHERE id = ".$hackerdata['clan_id']);
	if ($pass1 != "") $result = mysqli_query($link, "UPDATE clan SET bankaccount_password = '$password_hashed' WHERE id = ".$hackerdata['clan_id']);
	PrintMessage ("Success", "Clan updated.", "40%");
	include ("pages/claninfo.php");
?>	
