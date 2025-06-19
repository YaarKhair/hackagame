<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    if ($goal_id < 1 || $goal_id > 3) return "Invalid goal";
    // 1 == hack a gateway
    // 2 == hack a server
    // 3 == hack a hacker
		//if (!IsUnhackable($hackerdata['id'])) return "this features is currently being redesigned.";

    // hacker checksec
    if ($hackerdata['network_id'] != 2) return "You are not connected to the internet.";
    if ($hackerdata['clan_id'] == 0) return  "DDosing requires you to be in a clan.";
    
    // is your own gateway online? no gateway, no server attacks
    if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "Your gateway is offline!";
    
    // clan ready for server hack?
    $result = mysqli_query($link, "SELECT nextserverhack_date, nextpchack_date, nextgatewayhack_date FROM clan WHERE id = ".$hackerdata['clan_id']);
    $row = mysqli_fetch_assoc($result);
    
    if ($goal_id == 1) {
        if ($now < $row['nextgatewayhack_date']) return "You and your clan can retry attacking a gateway in ".Seconds2Time(SecondsDiff($now, $row['nextgatewayhack_date']));
        $goal = "Attack and take down a Gateway";
        $page = "clanhackgw";
				
				// if no clan was selected yet
				if ($clan_id == 0)
				{
					$message = "";
					$clan_result = mysqli_query ($link, "SELECT id, alias FROM clan WHERE active = 1 AND id <> {$hackerdata['clan_id']} ORDER BY alias ASC");
					if (!$clan_result || mysqli_num_rows($clan_result) == 0) return "No clans found.";
					while ($row = mysqli_fetch_assoc($clan_result))
						$message .= "<a href=\"?h=clanhackgw&clan_id={$row['id']}\">{$row['alias']}</a><br>";
					return ("<h1>Pick the clan you are planning to attack!</h1><br>$message");
				}
    }
    if ($goal_id == 2) {
        if ($now < $row['nextserverhack_date']) return "You and your clan can retry attacking a server in ".Seconds2Time(SecondsDiff($now, $row['nextserverhack_date']));
        $goal = "Attack and take down a Server";
        $page = "clanhackserver";
    }
    if ($goal_id == 3) {
        if ($now < $row['nextpchack_date']) return "You and your clan can retry attacking a pc in ".Seconds2Time(SecondsDiff($now, $row['nextpchack_date']));
        $goal = "Attack and take down a Hacker";
        $page = "clanhackpc";
    }

    
    echo '          
        <h1>DDoS Attack</h1>';
	PrintMessage ("Info", "To participate in a DDOS attack you must not open other HF pages until after the DDOS attack is launched, because doing so will cancel your participation. Stay on this page until the attack is launched.");
        echo '<div class="row light-bg">
            Last refresh: '.Number2Date($now).' [<a href="?h='.$page.'">Refresh now!</a>]<br>
            <div class="hr-light"></div>
            <h2>Goal: '.$goal.'</h2>
            <br><br>
           	DDOS-status:<ul>';
        
    $counter = 0;
    $error = "";
    $warning = "";
    
	// for a gw attack, see if 3 servers are infected with Gateway IP Tracer
	// outside of the while loop, because it's only the number that counts, not who owns the infections
	if ($goal_id == 1) 
	{
		// see which servers you have infected with the gateway finder and sort them by clan
		$result = mysqli_query($link, "SELECT COUNT(infection.id) as numServers FROM infection LEFT JOIN server ON infection.victim_id = server.id LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE hacker.clan_id = $clan_id AND infection.product_id = {$PRODUCT['Gateway Finder']}");

		if (!$result || mysqli_num_rows($result) == 0) $num_servers = 0;
		else 
		{
			$row = mysqli_fetch_assoc ($result);
			$num_servers = $row['numServers'];
			$clan_alias = ShowClanAlias ($clan_id);
			echo "Target: $clan_alias<br>";
		}
		if ($num_servers < $gateway_servercount)	
		{
			$error .= "<li>Your clan needs at least $gateway_servercount servers infected with the Gateway IP Tracer.</li>";
		 	echo "NOT READY (infections)";
		}
	}

    // check the team
    $online = date($date_format, strtotime("-$offline_limit minutes"));
    $result = mysqli_query($link, "SELECT id, alias FROM hacker WHERE clan_id = ".$hackerdata['clan_id']." AND current_page = '$page' AND last_click >= '$online' ORDER BY ep DESC");
    
    while ($row = mysqli_fetch_assoc($result)) {
		$alias = ShowHackerAlias($row['id']);
		$player_ready = true; // lets presume this player is ready
        echo "<li><strong>$alias:</strong><br>";
        
		//gw
        if ($goal_id == 1) {
            if (!HasOnHDD($row['id'], 69)) {
                $player_ready = false;
                $error .= "<li>$alias does not have a Gateway Destroyer on his HDD.</li>";
                echo "NOT READY (tools)";
            
            }
            if (!HasOnHDD($row['id'], 102)) $warning = "Not all teammembers have the Defacing tool. The clan page will not be defaced!";
        }
		
		// server
        if ($goal_id == 2) {
            if (!HasOnHDD($row['id'], 19)) {
                $player_ready = false;
                $error .= "<li>$alias does not have a Brute Force tool on his HDD.</li>";
                echo "NOT READY (tools)";
            
            }
        }
		
		// pc
        if ($goal_id == 3) {
            if (!HasOnHDD($row['id'], 16)) {
                $player_ready = false;
                $error .= "<li>$alias does not have a Connection Destroyer on his HDD.</li>";
                echo "NOT READY (tools)";
            
            }
        }
		
		// general check: do they have a server?
        if (NumServers($row['id']) == 0) {
            $player_ready = false;
            $error .= "<li>$alias does not own a server.</li>";
            echo "NOT READY (server)";
        }
		
        if ($player_ready) echo "READY!";
        echo "</li>";
		
        $counter ++;
        if ($counter == 1) {
            $leader_id = $row['id']; // highest ranked player is the leader
            $leader_alias = $row['alias'];
        }   
            
        $id[$counter] = $row['id'];
    }   
    echo '</ul><br>';
    echo 'Total in room: '.$counter;
    echo '<br><br><div class="hr-light"></div>';
    
    if ($counter > $clanhack_maxsize) $error .= "<li>Too many members in the group. $clanhack_maxsize is the maximum size.</li>";
    if ($counter < $clanhack_minsize) $error .= "<li>Not enough members in the group. At least $clanhack_minsize are required.</li>";

	if ($error == "") {
        $_SESSION['doclanhack'] = 1;
        if ($leader_id != $hackerdata['id']) echo "Waiting for $leader_alias to initiate the attack..";
        else {
        	if ($warning != "") PrintMessage ("warning", $warning); // warn the leader defacing will not work as not all participants have the defacement tool
            // show initiation form
            echo 'You are appointed as the leader of this attack; therefore, you must initiate the attack.
                <form method="POST" action="index.php">
                    <input type="hidden" name="h" value="doclanhack">
                    <input type="hidden" name="goal_id" value="'.$goal_id.'">';
            if ($goal_id == 1) {
            	if ($warning == "") echo 'Defacing message:<br><textarea cols="70" rows="30" name="extra_info"></textarea><br>';
							echo '<input type="hidden" name="clan_id" value="'.$clan_id.'">';
            }
            if ($goal_id == 2) echo 'Server IP: <input type="text" name="server_ip" size="15" maxlength="15"><br>';
            if ($goal_id == 3) {
                echo 'Hacker Alias: <input type="text" name="hacker_alias" size="15" maxlength="50"><br>';
                echo 'Hacker IP: <input type="text" name="hacker_ip" size="15" maxlength="150"><br>';
            }   
            echo '
                    <input type="submit" value="Initiate Ddos Attack">
                </form>';   
        }
    }
    else PrintMessage ("Error", "You need to correct the following errors before you can attempt this DDOS attack: <ul>$error</ul>");
    echo '</div>';
?>