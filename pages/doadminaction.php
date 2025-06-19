<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	// no idea where to store this query. so here for now. it will sum all donations per month:
	// select substr(date, 1, 6) as yearmonth, sum(substr(details, 12, 2)) from log where event = "staff" and details like "Premium%" group by yearmonth
	include 'modules/permissions.php';
	
	$id = 0;
	$clan_id = 0;
	$ban = 0;
	$reason = "";
	$message = "";
	$imtext = "";
	//$im = true; // send the user an IM in some cases
	$till = '';
	$email = 0; // dont send an email
    
	if (!empty($_REQUEST['id']))  $id = intval($_REQUEST['id']); 
	if (!empty($_REQUEST['clan_id']))  $clan_id = intval($_REQUEST['clan_id']); 
	if (!empty($_REQUEST['war_id']))  $war_id = intval($_REQUEST['war_id']); 
	if (!empty($_REQUEST['action']))  $action = sql($_REQUEST['action']);
	if (!empty($_REQUEST['reason']))  $reason = sql($_REQUEST['reason']);
	if (!empty($_REQUEST['till']))  $till = intval($_REQUEST['till']);
	if (!empty($_REQUEST['entity']))  $entity = sql($_REQUEST['entity']);
	if (!empty($_REQUEST['group_id']))  $group_id = intval($_REQUEST['group_id']);
	if (!empty($_REQUEST['addremove']))  $addremove = intval($_REQUEST['addremove']);
	if (!empty($_REQUEST['ip']))  $ip = sql($_REQUEST['ip']);
	if (!empty($_REQUEST['email'])) $email = sql($_REQUEST['email']);
	
	if ($reason == "[reason]") return "You did not specify a reason!";
	
	if($action == 'changeemail') 
	{
		 if($is_mod && (InGroup($id, 1) || InGroup($id, 2))) return "Mods cannot change admin or other mod emails.";
		 
		 // Get the old email and prepare a message
		 $old_email = mysqli_get_value("email", "hacker", "id", $id);
		 $IM = "Your email was changed from $old_email to $email by {$hackerdata['alias']} <br> If this change was not wanted, please report it to an administrator. <br> You also need to use your new email to login now.";
		 $title = "Email change";
		 
		 // Log it, send an IM and an email to him
		 SendIM(0, $id, "Email change", $IM, $now);
		 SendMail($old_email, $title, $IM);
		 
		 // Reset it now and display a message
		 ResetEmail($id, $email);
		 $message .= "Email change: from [$old_email] to [$email]";
	}
	
	// get info on this hacker
	if ($id > 0) 
	{
		$result = mysqli_query($link, "SELECT * FROM hacker WHERE id = ".$id);
        $redirect = "profile";    // most likely a hacker related action
	}    
	if ($clan_id > 0)	
	{
		$result = mysqli_query($link, "SELECT * FROM clan WHERE id = ".$id);
		$redirect = "claninfo";
	}    
		
	$row = mysqli_fetch_assoc($result);
	
	/* ADMIN ONLY OPTIONS! */
	if (InGroup($hackerdata['id'], 1)) 
	{
		if ($action == "killclan") 
		{
			KillClan($clan_id, $reason);
			$message = "Clan killed";
		}
		if ($action == "whitelist" || $action == "blacklist") 
		{
			$result = mysqli_query($link, "INSERT INTO $action (ip, reason) VALUES ('$ip', '$reason')");
			$message = $ip." ".$action."ed from the game";
			$imtext = $ip." is now ".$action."ed.";
		}
		if ($action == "makepremium")
		{
			// premium
			$amount = 0;
			if (!empty($_REQUEST['amount']))  $amount = intval($_REQUEST['amount']); 
			$periods = 1;
			if (!empty($_REQUEST['periods']))  $periods = intval($_REQUEST['periods']); 

			$months = $periods * $premium_time; // total months from today you are premium
			
			// are you still premium? then add the new donation time to the old
			$donator_till = mysqli_get_value ("donator_till", "hacker", "id", $id);
			if ($donator_till > $now)
				$from = $donator_till;
			else
				$from = $now;
				
			$timestamp = strtotime("+".$months." months", strtotime($from));
			$donator_till = date($date_format, $timestamp);
			
			$result = mysqli_query($link, "UPDATE hacker SET donator = 1, donator_till = '$donator_till', nextalias_date = '0' WHERE id = $id"); // premium
			$message = "Premium for $months months. Donated $amount";
			$imtext = "Your account type is now set to Premium for $months months. Thank you VERY MUCH for your donation of $currency $amount!!!";
			SendIM (0, $id, "Premium Hacker", "Your [[Premium Hacker]] status ends today.", $donator_till);
		}
		if ($action == "dogroup") 
		{
			if ($group_id == 1 && $id == 1 ) die ("You can not remove this user from the Admin group!");
			// captcha
			if ($addremove == 1) 
			{
				$result = mysqli_query($link, "INSERT INTO hacker_permgroup (hacker_id, permgroup_id) VALUES ($id, $group_id)");
				$message = "Added to the group ".mysqli_get_value("name", "permgroup", "id", $group_id);
				$imtext = "You were added to the group ".mysqli_get_value("name", "permgroup", "id", $group_id);
			}	
			else 
			{
				$result = mysqli_query($link, "DELETE FROM hacker_permgroup WHERE hacker_id = $id AND permgroup_id = $group_id");
				$message = "Removed from the group ".mysqli_get_value("name", "permgroup", "id", $group_id);
				$imtext = "You were removed from the group ".mysqli_get_value("name", "permgroup", "id", $group_id);
			}	
		}
	}	
	/* END OFF ADMIN ONLY OPTIONS! */
	
	// bug hunters can do this too (bug hunters are actually bot hunters)
	if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2) || InGroup($hackerdata['id'], 5)) 
	{
		if ($action == "resetshit") 
		{
			// captcha
			$result = mysqli_query($link, "DELETE FROM log WHERE event = 'interval' AND hacker_id = $id"); // clear SHIT
			$message = "SHIT list reset";
		}
	}
	
	// admins and mods only
	if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) 
	{
		if ($action == "disable") 
		{
			// disable account
			$result = mysqli_query($link, "UPDATE hacker SET hybernate_from = 0, hybernate_till = 0, email = concat('**DISABLED**', email) , password = '', real_ip = '".randomip()."', support_tickets = -1, clan_id = 0, clan_council = 0 WHERE id = $id");
			$result = mysqli_query($link, "SELECT id FROM server WHERE hacker_id = $id");
			if (mysqli_num_rows($result) > 0) 
			{
				while ($row = mysqli_fetch_assoc($result)) DropServer ($row['id'], $id, "Account disabled");
			}	
			$message = "Account disabled";
		}
		if ($action == "emptybank") 
		{
			// manually activate a hacker
			$result = mysqli_query($link, "UPDATE hacker SET bankaccount = 0 WHERE id = ".$id);
			$message = "Bank emptied ($reason)";
			$imtext = "Your bank account was emptied.";
		}
		if ($action == "enable") 
		{
			// enable account
			$row['email'] = substr($row['email'], 12, strlen($row['email']) - 12);
			$result = mysqli_query($link, "UPDATE hacker SET hybernate_from = 0, hybernate_till = 0, email = '{$row['email']}' , password = '', real_ip = '".randomip()."', support_tickets = 100, clan_id = 0 WHERE id = $id");
			$message = "Account enabled";
			$imtext = "Your account is now enabled. Please request a new password using the link below:<br><a href=\"$gameurl/guest.php?h=resetpass\">http://www.hackerforever.com/guest.php?h=resetpass</a>";
			$email = 1;
		}
		if ($action == "ban") 
		{
			// ban hammer
			Ban ($id, $reason, $hackerdata['id']);
			$message = "Banned ($reason)";
			$imtext = "You are banned from the game.";
			$email = 1;
		}
		if ($action == "unban") 
		{
			// unban
			Unban ($id);
			$message = "Unbanned";
			$imtext = $hackerdata['alias']." has unbanned you from the game.";
			$email = 1;
		}
    	if ($action == "endwar") 
		{
            $result = mysqli_query($link, "DELETE FROM war WHERE id = $war_id");
            $message = "War forcefully ended";
            //$im = "One of your ads was removed.";
    	}
    	if ($action == "removead") 
		{
            $result = mysqli_query($link, "DELETE FROM ad WHERE id = $id");
            $message = "Ad removed";
            $im = "One of your ads was removed.";
            $redirect = "ad";
    	}
		if ($action == "setcaptcha") 
		{
			// captcha
			$result = mysqli_query($link, "UPDATE hacker SET nextcaptcha_date = '".$till."' WHERE id = $id"); // premium
			$message = "Captcha free until ".Number2Date($till);
    		if ($till > 0) $imtext = "You are captcha free until ".Number2Date($till);
		}
		if ($action == "resetdupe") 
		{
			// dupe score
			$result = mysqli_query($link, "UPDATE hacker SET duplicate_score = 0 WHERE id = ".$id); 
			$message = "Duplicate score reset";
		}	
		if ($action == "resetbot") 
		{
			// bot score
			$result = mysqli_query($link, "UPDATE hacker SET bot_score = 0 WHERE id = ".$id); 
			$message = "Bot score reset";
		}	
		if ($action == "activate") 
		{
			// manually activate a hacker
			$result = mysqli_query($link, "UPDATE hacker SET activationcode = '' WHERE id = ".$id);
			$message = "Account activated";
			$imtext = "Your account was activated.";
			$email = 1;
			PlayerTutorial($id, 0); // give them the first email with some general info
		}
		if ($action == "jail") 
		{
			// jail
			$result = mysqli_query($link, "UPDATE hacker SET jailed_from = '$now', jailed_till = '$till', jailed_reason = '$reason', jailed_bail = 0 WHERE id = $id");
			$message = "Jailed until ".Number2Date($till)." ($reason)<br>";
			$imtext = "You were jailed.<br>";
			$action = "kick"; // staff jail also means chatkicked
			
			// additionally destroy their system and deprive them of server revenue
			$result2 = mysqli_query($link, "DELETE FROM system WHERE product_id NOT IN (SELECT id FROM product WHERE code = 'CPU' OR code = 'MEMORY' or code = 'MAINBOARD' or code = 'INTERNET') AND hacker_id = $id"); // hdd gone and all installed software
			$result2 = mysqli_query($link, "DELETE FROM inventory WHERE server_id = 0 AND hacker_id = $id"); // software on it gone
			AddLog ($id, "hacker", "system", "System taken by game administration.", $now);
			
		}
		if ($action == "unjail") 
		{
			// set free
			$result = mysqli_query($link, "UPDATE hacker SET jailed_till = '$now' WHERE id = ".$id); // unjail the player
			$message = "Freed from jail";
			$imtext = "You were freed from jail.";
			$action = "unkick"; // lets also unkick him/her from chat
		}
		if ($action == "unprison") 
		{
			// set free
			$result = mysqli_query($link, "UPDATE hacker SET prison_till = '$now' WHERE id = ".$id); // unjail the player
			$message = "Freed from prison";
			$imtext = "You were freed from prison.";
		}
		if ($action == "unlock") 
		{
			// unlock locked account (x times wrong password)
			$result = mysqli_query($link, "UPDATE hacker SET failed_logins = 0 WHERE id = ".$id); // unjail the player
			$message = "Account unlocked";
			$imtext = "Your account was unlocked.";
			$email = 1;
		}
		if ($action == "kick") 
		{
			// kick
			$result = mysqli_query($link, "UPDATE hacker SET chatkick_from = '$now', chatkick_till = '$till', chatkick_reason = '$reason' WHERE id = ".$id);
			$message .= "Chatkicked until ".Number2Date($till)." ($reason)";
			$imtext .= "You were kicked from the chat.";
		}
		if ($action == "unkick") 
		{
			// set free
			$result = mysqli_query($link, "UPDATE hacker SET chatkick_till = '$now' WHERE id = ".$id); // unkick the player
			$message .= "Unkicked from chat.";
			$imtext .= "You were unkicked from the chat.";
		}	
		if ($action == "hibernate") 
		{
			// hibernate
			$result = mysqli_query($link, "UPDATE hacker SET hybernate_from = '$now', hybernate_till = '$till' WHERE id = ".$id);
			$message = "Hibernated until ".Number2Date($till);
			$imtext = "You are set to hibernation.";
			$email = 1;
		}
		if ($action == "unhibernate") 
		{
			// set free
			$result = mysqli_query($link, "UPDATE hacker SET hybernate_till = '$now' WHERE id = ".$id); // unhibernate the player
			$message = "Unhibernated";
			$imtext = "Your hibernation was ended.";
			$email = 1;
		}	
		if ($action == "clearprofile") 
		{
			// clear profile
	
			$result = mysqli_query($link, "UPDATE hacker SET extra_info = '' WHERE id = ".$id);
			$message = "Profile cleared ($reason)";
			$imtext = "Your profile text was cleared.<br>Reason: $reason";
		}
		if ($action == "deleteavatar") 
		{
			DeleteAvatar ($id, $entity);
			$message = "$entity avatar removed ($reason)";
			$imtext = "Your $entity avatar was removed.<br>Reason: $reason";
		}	
        if ($action == "resetnetwork") 
		{
            if ($row['clan_id'] > 0) RemoveFromClan ($id, $row['clan_id'], "Reset to n00bNET");
    		$result2 = mysqli_query($link, "DELETE FROM system WHERE system.product_id IN (SELECT id FROM product WHERE code = 'INTERNET') AND hacker_id = $id");
    		$result2 = mysqli_query($link, "UPDATE hacker SET network_id = 1, bankaccount = bankaccount - $startmoney WHERE id = $id");
    		$message = "Reset to n00bNET, $currency $startmoney withdrawn";
			$imtext = "Your connection was reset n00bNET and $currency $startmoney was withdrawn from your bank account.";
        }
	}
	
	AddLog ($id, "hacker", "staff", $message." [{$hackerdata['alias']}]", $now);
	
	// print the result
	if ($imtext != "")  
	{
		$subject = "Message From Game Administration";
		SendIM(0, $id, $subject, $imtext, $now);
		$message .= "<br>Hacker was notified via IM.";
		if ($email == 1) 
		{
			$mailresult = SendMail($row['email'], $subject, $imtext); //external mail
			$message .= "<br>Hacker was also notified via Email.";
		}
	}
	
	if ($message == "") PrintMessage("error", "I'm pretty sure you are not allowed to do that."); // if there is no message from the script, it means it was no valid action for this user.
    else PrintMessage ("Success", $message, "40%");
    
    if ($redirect == "claninfo") $id = $clan_id;
	if ($redirect == "profile") $id = $id;
	include ("./pages/$redirect.php");
?>