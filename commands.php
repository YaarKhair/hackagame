<?php
    // YOUR ARE SENDING A MESSAGE, LETS SEE IF IT INCLUDES A SPECIAL CHAT COMMAND
        // update last lastchatmsg_date
        $result = mysqli_query($link, "UPDATE hacker SET last_click = '$now', lastchatmsg_date = '$now' WHERE id = {$hackerdata['id']}");

        // Commands for admins and mods
		if ($is_admin) {
			if (GetCommand('/fake')) {
				$fake = explode("|", $msg);
				if (strtolower($fake[1]) != "chaozz") $hackerdata['alias'] = $fake[1];
				$msg = $fake[2];
				// fake chatcolors
				$result = mysqli_query($link, "SELECT hacker.id, hacker.chat_color, hacker.last_login, hacker.started, clan.color, clan_id, chatkick_till, banned_date, jailed_till, prison_till, offline_from, offline_till, activationcode, hybernate_till, onchatpage_date FROM hacker LEFT JOIN clan on hacker.clan_id = clan.id WHERE hacker.alias = '{$hackerdata['alias']}'");
				$fakedata = mysqli_fetch_assoc($result);
				if ($fakedata['clan_id'] > 0) $alias_color = "{$fakedata['color']}";
				else $alias_color = "FFFFFF";
				// donators can have a chat text color
				if ($fakedata['chat_color'] != "") $msg_color = "{$fakedata['chat_color']}";
				else $msg_color = 'FFFFFF';
			}	
			if (GetCommand('/banhammer')) {
				// funny pick
				$ibot_msg = '<img src="images/banhammer.png">';
				$msg = ""; // kill public output
			}
			if (GetCommand('/img')) {
				// any picture
				$pic = explode("|", $msg);
				$ibot_msg = '<img src="'.$pic[1].'">';
				$msg = ""; // kill public output
			}
		}

		// Commands for admins, mods and supporters
		if	($is_staff || $is_supporter) {
			if (GetCommand('/language')) {
				$reason = explode("|", $msg);
				$ibot_msg = '**Please watch your language!**';
				
				$msg = ''; // Kill public output
			}	
			if (GetCommand('/english')) {
				$reason = explode("|", $msg);
				$ibot_msg = '**This chat is english only!**';
				
				$msg = ''; // Kill public output
			}	
			if (GetCommand('/warn')) {
				// /warn|hacker|reason
				$warn = explode("|", $msg);
				$ibot_msg = '/me **WARNED '.$warn[1].', reason: '.$warn[2].'**';
				
				$msg = ''; // Kill public output
			}
			if (GetCommand('/kick')) {
				// /kick|hacker|reason
				$kick = explode("|", $msg);
				$kicked_until = date($date_format, strtotime("+".$initial_floodkick." minutes"));
				$result = mysqli_query($link, "UPDATE hacker SET chatkick_reason = '".$kick[2]."', chatkick_from = '$now', chatkick_till = '$kicked_until' WHERE alias = '".$kick[1]."'");
				$ibot_msg = '/me **KICKED '.$kick[1].', reason: '.$kick[2].'**';
				
				// add a log
				$kicked_id = mysqli_get_value ("id", "hacker", "alias", $kick[1], false);
				$message = "Chatkicked until ".Number2Date($kicked_until)." ({$kick[2]})";
				AddLog ($kicked_id, "hacker", "staff", $message." [{$hackerdata['alias']}]", $now);
				
				$msg = ''; // Kill public output
			}
		}
		
        
		// Commands for everyone
		if (GetCommand('/bank')) {
			 $amount = mysqli_get_value("bankaccount","hacker","id",$ibot_id);
			 $amount = $double_ep_amount - $amount;
			 if ($amount > 0) $ibot_msg =  "You need to feed me $currency".number_format($amount)." more to trigger a double EP day!";
			 else $ibot_msg =  "Tomorrow is double EP day!! HURRAY!";
		}
		if (GetCommand('/jackpot')) {
			$result = mysqli_query($link, "SELECT id FROM lottery");
			if (mysqli_num_rows($result) > 0) {
				$tickets_sold = mysqli_num_rows($result);
				$ibot_msg = "The jackpot is: $currency".number_format($tickets_sold * $lottery_price);
			}	
			else $ibot_msg .= "The current jackpot is: 0 (no tickets sold)";
		}
		if (GetCommand('/ctf')) {
			$ibot_msg = "The File Holder is: ".ShowFileHolder();
		}
		if (GetCommand('/jail')) {
			$ibot_msg = "Jailed: ";
			$result = mysqli_query($link, "SELECT alias, jailed_bail FROM hacker WHERE jailed_from <= '$now' AND jailed_till >= '$now'");
			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) $ibot_msg .= $row['alias']." ($currency".number_format($row['jailed_bail'])."), ";
				$ibot_msg = substr($ibot_msg, 0, -2);
			}	
			else $ibot_msg .= "No jailbirds at this moment.";
		}
		if (GetCommand('/prison')) {
			$ibot_msg = "Imprisoned: ";
			$result = mysqli_query($link, "SELECT hacker.alias FROM hacker WHERE prison_from <= '$now' AND prison_till >= '$now'");
			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) $ibot_msg .= $row['alias'].", ";
				$ibot_msg = substr($ibot_msg, 0, -2);
			}	
			else $ibot_msg .= "No prisoners atm.";
		}
		if (GetCommand('/fbi')) {
			$ibot_msg = "Most Wanted: ";
			$result = mysqli_query($link, "SELECT alias FROM hacker WHERE npc = 0 AND fbi_wanteddate > 0 ORDER BY ep DESC");
			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) $ibot_msg .= $row['alias'].", ";
				$ibot_msg = substr($ibot_msg, 0, -2);
			}	
			else $ibot_msg .= "No hackers on the most wanted list at this moment.";
		}
		if (GetCommand('/level')) {
			$name = sql(trim(substr($msg, 6, strlen($msg) -6))); // strip the command
            if ($name == "") $name = "#$&*"; // non existant. and empty name will generate hits, because npcs get retiFF0000 that way.
			$result = mysqli_query($link, "SELECT hacker.id FROM hacker WHERE alias = '$name'");
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				if (InGroup($row['id'], 1) || InGroup($row['id'], 2)) $ibot_msg = "You can not query staff members.";
				else {
					$level = GetHackerLevel($row['id']);
					if ($level == 42) $level = "<a href=\"https://www.youtube.com/watch?v=gt7mtdLha-c\">42</a>";
					$ibot_msg = "$name is level ".$level;
				}
			}	
			else $ibot_msg = "Not found.";
		}
		if (GetCommand('/lastactive')) {
			$name = sql(trim(substr($msg, 11, strlen($msg) -11))); // strip the command
            if ($name == "") $name = "#$&*"; // non existant. and empty name will generate hits, because npcs get retiFF0000 that way.
			$result = mysqli_query($link, "SELECT last_click FROM hacker WHERE alias = '$name'");
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				$ibot_msg = "$name was last seen at ".Number2Date($row['last_click']);
			}	
			else $ibot_msg = "Not found.";
		}
		if (GetCommand('/status')) {
			$name = sql(trim(substr($msg, 7, strlen($msg) -7)));
            if ($name == "") $name = "#$&*"; // non existant. and empty name will generate hits, because npcs get retiFF0000 that way.
			else {
				$result = mysqli_query($link, "SELECT hacker.id FROM hacker WHERE alias = '$name'");
				if (mysqli_num_rows($result) > 0) {
					$row = mysqli_fetch_assoc($result);
					$ibot_msg = "The system of $name is ".GetStatus($row['id']);
				}	
				else $ibot_msg = "Not found.";
			}
		}
/*		if (GetCommand('/rank')) {
			$name = sql(trim(substr($msg, 5, strlen($msg) -5)));
            if ($name == "") $name = "#$&*"; // non existant. and empty name will generate hits, because npcs get retiFF0000 that way.
			if (in_array(strtolower($name), $admin) || in_array(strtolower($name), $mod)) $ibot_msg = "That is secret intel";
			else {
				$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$name'");
				if (mysqli_num_rows($result) > 0) {
					$row = mysqli_fetch_assoc($result);
					if (InGroup($row['id'], 1) || InGroup($row['id'], 2)) $ibot_msg = "You can not query staff members.";
					else $ibot_msg = "$name is ranked #".number_format(GetWorldRank($row['id']))." of the world.";
				}	
				else $ibot_msg = "Not found.";
			}
		}*/
		if (GetCommand('/clan')) {
			$name = sql(trim(substr($msg, 5, strlen($msg) -5)));
            if ($name == "") $name = "#$&*"; // non existant. and empty name will generate hits, because npcs get retiFF0000 that way.
			$result = mysqli_query($link, "SELECT clan.id, clan.alias FROM hacker LEFT JOIN clan ON hacker.clan_id = clan.id WHERE hacker.alias = '$name'");
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				if ($row['alias'] != "") $ibot_msg = "$name is member of the clan <a href=\"?h=claninfo&id={$row['id']}\">{$row['alias']}</a>";
        		else $ibot_msg = "$name is not in a clan";
			}	
			else $ibot_msg = "Not found.";
		}
		if (GetCommand('/afk')) {
			$result = mysqli_query($link, "UPDATE hacker SET lastchatmsg_date = 0 WHERE id = {$hackerdata['id']}");
		}
		if (GetCommand('/high5')) {
			$name = sql(trim(substr($msg, 6, strlen($msg) -6)));
			if (strtolower($name) == strtolower($hackerdata['alias'])) $ibot_msg = "/me sees {$hackerdata['alias']} highfive himself, forever alone..";
			//elseif (strtolower($name) == "chaozz" || strtolower($name) == "ibot") $ibot_msg =  "/me runs to $name then turns around and slaps {$hackerdata['alias']} in the face..";
			else {
				$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$name'");
				if (mysqli_num_rows($result) > 0) {
					$row = mysqli_fetch_assoc($result);
					$ibot_msg = "/me runs to $name and gives him a high five..";
				}	
				else $ibot_msg = "/me slaps {$hackerdata['alias']} in the face..";
			}
		}	
		if (GetCommand('/slap')) {
			$name = sql(trim(substr($msg, 5, strlen($msg) -5)));
			if (strtolower($name) == strtolower($hackerdata['alias'])) $ibot_msg = "/me sees {$hackerdata['alias']} punch himself";
			elseif (strtolower($name) == "chaozz" || strtolower($name) == "ibot") $ibot_msg =  "/me runs to $name then turns around and slaps {$hackerdata['alias']}";
			else {
				$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$name'");
				if (mysqli_num_rows($result) > 0) {
					$row = mysqli_fetch_assoc($result);
					$ibot_msg = "/me runs to $name and slaps him";
				}	
				else $ibot_msg = "/me slaps {$hackerdata['alias']}";
			}
			$ibot_msg .= " in the face..";
		}	
		if (GetCommand('/wiki')) {
			// now see if there is a wiki about this
			$subject = sql(strtolower(trim(substr($msg, 5, strlen($msg) -5))));
			if (trim($subject) != "") $ibot_msg = "Nothing to search for.";
			$result = mysqli_query($link, "SELECT title FROM wiki WHERE title = '$subject' LIMIT 1"); // exact title
			if (mysqli_num_rows($result) == 0) $result = mysqli_query($link, "SELECT title FROM wiki WHERE tags LIKE '%$subject%' LIMIT 1"); // part of tags
			if (mysqli_num_rows($result) == 0) $result = mysqli_query($link, "SELECT title FROM wiki WHERE title LIKE '%$subject%' LIMIT 1"); // part of title
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				$ibot_msg =  "Wiki article found. <a href=\"/?h=wiki&title={$row['title']}\">Click here</a>.";
			}	
			else $ibot_msg = "Wiki article not found.";
		}
		if (GetCommand('/whisper') || GetCommand('/w')) {
			// /whisper|alias|msg
			$whisper = explode("|", $msg);
            $sendto_id = $hackerdata['id'];
            
			if ($whisper[1] == "") {
				$msg = "";
				$ibot_msg = "No alias supplied.";
                $ibot_sendto_id = $hackerdata['id'];
			}	
			else {	
				// target
                $result = mysqli_query ($link, "SELECT id FROM hacker WHERE alias = '{$whisper[1]}'");
                if (mysqli_num_rows($result) == 0) {
            		$msg = "";
	    			$ibot_msg = "Wrong alias supplied.";
                    $ibot_sendto_id = $hackerdata['id'];
                }
                else {
                    $row = mysqli_fetch_assoc($result);
                    
                    // on ignore list?
					if (OnList($row['id'], $hackerdata['id'], "ignore") && (!$admin && !$mod && !$chatmod)) {
	            		$msg = "";
		    			$ibot_msg = "Whisper was ignored.";
	                    $ibot_sendto_id = $hackerdata['id'];
					}
					else {	
	                    // send whisper via ibot to target
	    				$ibot_sendto_id = $row['id'];
	                	$ibot_msg = "{$hackerdata['alias']} << {$whisper[2]}";
	                    
	    	            // sender gets feedback too
	                    $receiver_id = $hackerdata['id'];
	                    $hackerdata['id'] = $ibot_id;
	                    $hackerdata['alias'] = $ibot_alias;
	                    $alias_color = $ibot_aliascolor;
	                    $msg_color = $ibot_msgcolor;
	                    $msg = "{$whisper[1]} >> {$whisper[2]}";
					}
                }
            }    
		}   
        if(GetCommand('/ignore')) {
            // /ignore chaozz
            $ibot_sendto_id = $hackerdata['id']; // message is just for the hacker issuing the /ignore command
            $alias = sql(strtolower(trim(substr($msg, 7, strlen($msg) -7))));
            $result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$alias' AND clan_id != $staff_clanid");
            $msg = ''; // kill public output
            if(mysqli_num_rows($result) > 0) {
	            $row = mysqli_fetch_assoc($result);
                $ignore_id = mysqli_get_value("id","hacker","alias",$alias, false);
                $result = mysqli_query($link, "INSERT INTO multi_list (hacker_id1, hacker_id2, relation) VALUES ({$hackerdata['id']},$ignore_id,'ignore')");
                $ibot_msg = "$alias was added to your ignore list. Use the friend/foe/ignore list to remove.";
            }
            else $ibot_msg = "$alias is not found and was not added to your ignore list.";
        }
?>