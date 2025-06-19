<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!empty($_GET['id'])) $id = intval($_GET['id']);
	if (!empty($id)) {
		$result = mysqli_query($link, "SELECT hacker.*, clan.alias as previous_clan, country.name FROM hacker LEFT JOIN clan ON hacker.previous_clanid = clan.id LEFT JOIN country ON hacker.country = country.code WHERE hacker.id = $id");
		$num_rows = mysqli_num_rows($result);
		if ($num_rows == 0)  
			return "Hacker not found."; 
		$row = mysqli_fetch_assoc($result);
		$previous_clan = $row['previous_clan'];
	}		
	else {
		$row = Array();
		$row = arrayCopy ($hackerdata);
		$result2 = mysqli_query($link, "SELECT alias FROM clan WHERE id = {$hackerdata['previous_clanid']}");
		if (mysqli_num_rows($result2) == 0) $previous_clan = "";
		else { $row2 = mysqli_fetch_assoc($result2); $previous_clan = $row2['alias']; }
	}
	
	// permissiongroups
	include("modules/permissions.php");
	
	// is this account disabled, but you're not a mod? then lets give an error
	if (substr($row['email'], 0, 2) == "**" && !$is_staff)
		return "You can not view profiles of disabled accounts.";
		
	$show_ipinfo = 0;
	if (!empty($_GET['show_ipinfo'])) $show_ipinfo = intval($_GET['show_ipinfo']);
	
	$code = sha1($hackerdata['started'].$hackerdata['last_login']);	
	
	// if you are viewing your own profile and your in a clan, you can leave it.
	if ($row['clan_id'] > 0 && $row['id'] == $hackerdata['id']) {
		if (IsFounder($hackerdata['id'])) $leaveclanlink = "&nbsp;&nbsp;&nbsp;&nbsp;[<a href=\"?h=leaveclan&code=$code\" onClick=\"return confirmSubmit('Are you sure? You are the clan founder, which means the clan will die if you leave!')\">Leave current clan</a>]";
		else $leaveclanlink = "&nbsp;&nbsp;&nbsp;&nbsp;[<a href=\"?h=leaveclan&code=$code\" onClick=\"return confirmSubmit('Are you sure? You will loose your servers, if any!')\">Leave current clan</a>]";
	}
	else $leaveclanlink = "";
	
	// ethic
	$ethic = GetEthic($row['ethic_id']);
	if ($row['id'] == $hackerdata['id']) $ethic.= '&nbsp;&nbsp;[<a href="?h=selectethic">Change Ethic</a>]';
	
	// number of servers
	if (!HasInstalled($hackerdata['id'],"SERVERSNIFFER")) $num_servers = "?";
	else $num_servers = NumServers($row['id']);	
	
	// optional extra permission group
	
	// level
	if (InGroup($row['id'], 1) || InGroup($row['id'], 2)) {
		$worldrank = "**HIDDEN**";
		$worldranklink = 0;
	}	
	else {
		$worldrank = number_format(GetWorldRank($row['id']));
		$worldranklink = GetWorldRank($row['id']);
	}	
	// public stats
	if ($row['publicstats'] == 0)
		$publicstats = "Disabled";
	else
		$publicstats = '[<a href="?h=personalstats&hacker_id='.$row['id'].'">Stats</a>]&nbsp;[<a href="?h=achievements&hacker_id='.$row['id'].'">Achievements</a>]';
		
	// extra info
	if ($row['extra_info'] != "") {
		$message = ReplaceBBC($row['extra_info']);
	}
	else $message = "//undefined;";
	
	echo '
		<table>
			<caption>Hacker Info</caption>
			<tbody>
				<tr><th width="15%">Alias:</th><td width="30%">'.ShowHackerAlias($row['id'], 0, false, true).'<br>'.ShowAvatar($row['id']).'</td>
				<td rowspan="16" width="*"><pre>'.$message.'</pre></div></td></tr>
				<tr><th>Extra group:</th><td>'.GetGroups($row['id']).'</td></tr>
				<tr><th>Ethic:</th><td>'.$ethic.'</td></tr>
				<tr><th>Country:</th><td><img src="./images/flags/'.$row['country'].'.png" title="'.$row['country'].'" /></td></tr>
				<tr><th>Joined:</th><td>'.Number2Date($row['started']).'</td></tr>
				<tr><th>Last Active:</th><td>'.Number2Date($row['last_click']).'</td></tr>
				<tr><th>System:</th><td>'.GetStatus($row['id']).'</td></tr>
				<tr><th>World Rank:</th><td><a href="?h=worldrank#'.$worldranklink.'">#'.$worldrank.'</a></td></tr>
				<tr><th>Hack Points:</th><td>'.$row['hackpoints'].'</td></tr>
				<tr><th>Level:</th><td>'.GetHackerLevel($row['id']).'</td></tr>
				<tr><th>Public Stats:</th><td>'.$publicstats.'</td></tr>
				<tr><th>Servers:</th><td>'.$num_servers.'/'.MaxServers($row['id']).'&nbsp;&nbsp;&nbsp;&nbsp;[<a href="?h=internet&subnet='.$row['alias'].'">Show</a>]</td></tr>
				<tr><th>Clan:</th><td>'.ShowClanAlias($row['clan_id'], false).$leaveclanlink.'</td></tr>
				<tr><th>Previous clan:</th><td>'.$previous_clan.'</td></tr>
				<tr><th>Previous alias:</th><td>'.$row['prev_alias'].'</td></tr>
				<tr><td colspan="2">';
				if ($row['id'] == $hackerdata['id']) {
					echo '<form method="POST" action="index.php">
							<input type="hidden" name="h" value="edithacker">
							<input type="submit" value="Edit My Profile">
						</form></center>';	
				}
				echo '</td></tr>
			</tbody>	
		</table>';
		
	// you can send an IM from here (if it's not your own profile)
	if ($row['id'] != $hackerdata['id']) {
		echo '
			<form method="POST" action="index.php">
				<input type="hidden" name="h" value="dowriteim">
				<input type="hidden" name="username" value="'.$row['alias'].'">
				<input type="submit" value="Send IM">
			</form>';	
	}
	
	echo '<br><br>';
	
	// ADMIN STUFF
	if (InGroup($hackerdata['id'], 1)) {
		echo '<form action="index.php" method="POST">
				<input type="hidden" name="h" value="doadminimmitate">
				<input type="hidden" name="action" value="immitate">
				<input type="hidden" name="hacker_id" value="'.$row['id'].'">
				<input type="submit" value="Immitate">
				</form>
			<div class="col50left">			
				<table>
					<caption>Admin Options</caption>
					<tbody>';
					// groups
					echo '
						<tr><th>Group:</th><td><form method="POST" action="index.php">
						<input type="hidden" name="h" value="doadminaction">
						<input type="hidden" name="action" value="dogroup">
						<input type="hidden" name="id" value="'.$row['id'].'">
						<select name="group_id">';
						$result2 = mysqli_query($link, "SELECT id, name FROM permgroup ORDER BY id ASC");
						if (mysqli_num_rows($result2) > 0) {	
							while ($row2 = mysqli_fetch_assoc($result2)) 
								echo "<option value=".$row2['id'].">".$row2['name'];
						}		
					echo '	
						</select>
						<select name="addremove">
							<option value="1">Add
							<option value="2">Remove
						</select>	
						<input type="submit" value="Submit">
						</form>
						</td></tr>';
					// donater
					echo '
						<tr><th>Premium Member:</th><td>';
						if ($row['donator_till'] > 0) echo '<strong>Donator until '.Number2Date($row['donator_till']).'</strong><br>';
						echo '<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							Amount donated: €<input type="text" name="amount" value=""><br>
							Periods (6 months): <input type="text" name="periods" value="1"><br>
							<input type="hidden" name="action" value="makepremium">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="submit" value="Make Premium Member">
							</form>';
					echo '</td></tr>';
					echo '
							<tr><th>IP '.$row['real_ip'].'</td><td>
							<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="whitelist">
							<input type="hidden" name="ip" value="'.$row['real_ip'].'">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="text" name="reason" value="[reason]">
							<input type="submit" value="Whitelist">
							</form>
							<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="blacklist">
							<input type="hidden" name="ip" value="'.$row['real_ip'].'">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="text" name="reason" value="[reason]">
							<input type="submit" value="Blacklist">
							</form>
							</td></tr>';
		echo '			
					</tbody>	
				</table>
			</div>';
	
		echo '
			<div class="col50right">';
				
				// HTTP HEADERS
				$base64 = mysqli_get_value("http_header", "hacker", "id", $row['id']); 
				$http_headers = unserialize(base64_decode($base64));
				$http_result = mysqli_query($link, "SELECT alias, id FROM hacker WHERE http_header = '$base64' AND id != {$row['id']} AND LENGTH(http_header) > 0 ORDER BY last_click DESC");
				$similar_header = '';
				while($http_row = mysqli_fetch_assoc($http_result)) $similar_header .= "<a href='index.php?h=profile&id={$http_row['id']}'>{$http_row['alias']}</a><br>";
				if(mysqli_num_rows($http_result) == 0) $similar_header = 'None found.';
				$http_headers['Similar Headers'] = $similar_header;
				
		echo '			
				<table>
					<caption>Header</caption>
					<tbody>';
					if($http_headers)
						foreach($http_headers as $header => $value) {
							if($header == 'Similar Headers') echo "<tr><td>$header</td><td>$value</td></tr>";
							else echo "<tr><td>$header</td><td>".wordwrap($value, 50, "<br>", true)."</td></tr>";
						}
					else
						echo "<tr><td>None recorded.</td></tr>";
		echo '
					</tbody>
				</table>
			</div>
			
			<br clear="all">';
	}	
	
	// MOD STUFF
	if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) {
		echo '<br><br>
			<div class="col50left">			
				<table>
					<caption>Mod Options</caption>
					<tbody>';
						if (substr($row['email'], 0, 2) == "**") { echo '<tr><td colspan=2>'; PrintMessage ("Error", "Please note this account is DISABLED"); echo '</td></tr>'; } // warn mods about a disabled account
						if (substr($row['email'], 0, 2) == "**") {
							echo '
							<tr><th>Enable account:</th><td>
							<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="enable">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="submit" value="Enable">
							</form>
							</td></tr>';
						}
						else {
							echo '
							<tr><th>Disable account:</th><td>
							<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="disable">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="submit" value="Disable">
							</form>
							</td></tr>';
						}
			$affiliate = 'None';
			if($row['aff_id'] > 0) $affiliate = mysqli_get_value("alias", "hacker", "id", $row['aff_id']);

		echo '		
	    				<tr><th>Email Address:</th><td><form action="index.php" method="POST"><input type="text" name="email" value="'.$row['email'].'"><input type="hidden" name="h" value="doadminaction"><input type="hidden" name="action" value="changeemail"><input type="hidden" name="id" value="'.$row['id'].'"><input type="submit" value="Change"></form></td></tr>
	    				<tr><th>Affiliate:</th><td>'.$affiliate.'</td></tr>
	                    <tr><th>Stats:</th><td>[<a href="?h=personalstats&hacker_id='.$row['id'].'">View</a>]</td></tr>
	    				<tr><th>Ban Info:</th><td>[<a href="?h=modbaninfo&id='.$row['id'].'">View</a>]</td></tr>
						<tr><th>ModLog:</th><td>[<a href="?h=modlog&entity=hacker&alias='.$row['alias'].'">View</a>]</td></tr>
						<tr><th>Duplicate Score:</th><td>'.$row['duplicate_score'].'&nbsp;[<a href="?h=modlog&entity=all&alias='.$row['alias'].'&event=staff&searchfor=duplicate%&limit=0">View</a>]&nbsp;[<a href="?h=doadminaction&action=resetdupe&id='.$row['id'].'">Reset</a>]</td></tr>
						<tr><th>Bot Score:</th><td>'.$row['bot_score'].'&nbsp;[<a href="?h=modlog&entity=all&alias='.$row['alias'].'&event=staff&searchfor=bot%&limit=0">View</a>]&nbsp;[<a href="?h=doadminaction&action=resetbot&id='.$row['id'].'">Reset</a>]</td></tr>
						<tr><th>Smallhack Interval:</th><td>&nbsp;[<a href="?h=modinterval&id='.$row['id'].'">View</a>]</td></tr>';					// network
						$network = mysqli_get_value ("name", "network", "id", $row['network_id']);
						
						if ($row['activationcode'] != "") {
							echo '
								<tr><th>Activation:</th><td><form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="activate">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="submit" value="Activate">
								</form>
								</td></tr>';
						}
						else echo '<tr><th>Activation:</th><td>Activated</td></tr>';
						echo '
						<tr><th>Alias:</th><td>'.$row['alias'].'</td></tr>
						<tr><th>Bank account:</th><td>'.number_format($row['bankaccount']).'</td></tr>
						<tr><th>Empty bank account:</th><td>
							<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="emptybank">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="text" name="reason" value="[reason]">
							<input type="submit" value="Empty">
							</form>
						</td></tr>
						<tr><th>EP:</th><td>'.$row['ep'].'</td></tr>
						<tr><th>Skill:</th><td>'.$row['skill'].'</td></tr>
						<tr><th>Network:</th><td>'.$network.'&nbsp;';
	
						// find IP friends :)
						$result2 = mysqli_query($link, "SELECT id, alias FROM hacker WHERE id <> {$row['id']} AND real_ip = '".$row['real_ip']."'");
						$ip_friends = "";
						while ($row2 = mysqli_fetch_assoc($result2)) $ip_friends .= '<br><a href="?h=profile&id='.$row2['id'].'">'.$row2['alias'].'</a>';
						if ($ip_friends != "") $ip_friends = "<br><br><strong><font color=\"red\">People also using this IP:</strong></font><i>".$ip_friends."</i><br>";
	
						// whitelisted? blacklisted? 
						$whitelisted = '';
						$result2 = mysqli_query($link, "SELECT reason FROM whitelist WHERE ip = '{$row['real_ip']}'");
						if (mysqli_num_rows($result2) > 0) {
							$row2 = mysqli_fetch_assoc($result2);
							$whitelisted = "<br><strong>WHITELISTED:</strong> ".$row2['reason']."<br>";
						}
						$blacklisted = '';
						$result2 = mysqli_query($link, "SELECT reason FROM blacklist WHERE ip = '{$row['real_ip']}'");
						if (mysqli_num_rows($result2) > 0) {
							$row2 = mysqli_fetch_assoc($result2);
							$blacklisted = "<br><strong>BLACKLISTED:</strong> ".$row2['reason']."<br>";
						}
	                    // reset to n00bnet
						if ($row['network_id'] == 2 && GetHackerLevel($row['id']) <= $noobnet_level) echo '<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="resetnetwork">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="submit" value="Reset to n00bnet">
							</form>';
	                    echo '</td></tr>';
	                    echo '<tr><th>Current page:</th><td>'.$row['current_page'].'</td></tr>
	    						<tr><th>Real IP:</th><td>'.$row['real_ip'].'</a>'.$ip_friends.$whitelisted.$blacklisted;
	    				if ($show_ipinfo == 1) echo '<br>IP Location: '.IPLocation($row['real_ip']);
	    				else echo '<br>[<a href="?h=profile&id='.$row['id'].'&show_ipinfo=1">Show IP info</a>]';
	    				echo '</td></tr>
	    						<tr><th>Game IP:</th><td>'.$row['ip'].'</td></tr>						
						';
						// show system
						echo '
							<tr><th>System:</th><td>';
						$result2 = mysqli_query($link, "SELECT product.title FROM system LEFT JOIN product ON system.product_id = product.id WHERE hacker_id = ".$row['id']);
						if (mysqli_num_rows($result2) > 0) {	
							while ($row2 = mysqli_fetch_assoc($result2)) 
								echo $row2['title'].'<br>';
						}
						echo '</td></tr>';
						// captcha
						echo '
							<tr><th>Captcha:</th><td><form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="setcaptcha">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="text" name="till" value="'.$row['nextcaptcha_date'].'">
							<input type="submit" value="Next Captcha Date">
							</form>
							</td></tr>';
						echo '
							<tr><th>Clear Profile Text:</th><td>
							<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="clearprofile">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="text" name="reason" value="[reason]">
							<input type="submit" value="Clear">
							</form>
							</td></tr>';
						echo '
							<tr><th>Delete avatar:</th><td>
							<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doadminaction">
							<input type="hidden" name="action" value="deleteavatar">
							<input type="hidden" name="entity" value="hacker">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="text" name="reason" value="[reason]">
							<input type="submit" value="Delete">
							</form>
							</td></tr>';
						echo '<tr><th>ACCOUNT LOCKOUT:</th>';
						if ($row['failed_logins'] > $bruteforce_limit) {
							// unlock account
							echo '
								<td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unlock">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="submit" value="Unlock Account">
								</form>
								</td></tr>';
						}
						else echo '<td>Not active</td></tr>';
						echo '<tr><th>PRISON:</th>';
						if ($row['prison_till'] > $now) {
							// free from prison
							echo '
								<td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unprison">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="submit" value="Free From Prison">
								</form>
								</td></tr>';
						}
						else echo '<td>Not in prison</td></tr>';
						
						if ($row['jailed_till'] < $now) {
							// JAIL!
							echo '
								<tr><th>JAIL:</th><td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="jail">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="text" name="reason" value="[reason]"><br>
								<input type="text" name="till" value="'.$now.'">
								<input type="submit" value="Jail">
								</form>
								</td></tr>';
						}
						else {
							// SET FREE!
							echo '
								<tr><th>Jailed From:</th><td>'.Number2Date($row['jailed_from']).'</td></tr>
								<tr><th>Jailed Till:</th><td>'.Number2Date($row['jailed_till']).'</td></tr>						
								<tr><th>Reason:</th><td>'.$row['jailed_reason'].'</td></tr>						
								<tr><th>JAIL:</th><td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unjail">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="submit" value="Set Free">
								</form>
								</td></tr>';
						}
						if ($row['chatkick_till'] < $now) {
							// KICK FROM CHAT!
							echo '
								<tr><th>CHAT&FORUM KICK:</th><td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="kick">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="text" name="reason" value="[reason]"><br>
								<input type="text" name="till" value="'.$now.'">
								<input type="submit" value="Chat&Forum Kick">
								</form>
								</td></tr>';
						}
						else {
							// UNKICK FROM CHAT!
							echo '
								<tr><th>Kicked From:</th><td>'.Number2Date($row['chatkick_from']).'</td></tr>
								<tr><th>Kicked Till:</th><td>'.Number2Date($row['chatkick_till']).'</td></tr>						
								<tr><th>Reason:</th><td>'.$row['chatkick_reason'].'</td></tr>						
								<tr><th>CHAT&FORUM KICK:</th><td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unkick">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="submit" value="Unkick">
								</form>
								</td></tr>';
						}
						if ($row['hybernate_till'] < $now) {
							// PUT IN HIBERNATION!
							echo '
								<tr><th>HIBERNATION:</th><td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="hibernate">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="text" name="till" value="'.$now.'">
								<input type="submit" value="Hibernate">
								</form>
								</td></tr>';
						}
						else {
							// UNHIBERNATE!
							echo '
								<tr><th>Hibernating From:</th><td>'.Number2Date($row['hybernate_from']).'</td></tr>
								<tr><th>Hibernating Till:</th><td>'.Number2Date($row['hybernate_till']).'</td></tr>						
								<tr><th>HIBERNATION:</th><td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unhibernate">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="submit" value="UnHibernate">
								</form>
								</td></tr>';
						}
						if ($row['banned_date'] == 0) {
							// BAN!
							echo '
								<tr><th>BAN HAMMER:</th><td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="ban">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="text" name="reason" value="[reason]">
								<input type="submit" value="Ban">
								</form>
								</td></tr>';
						}
						else {
							// UNBAN!
							echo '
								<tr><th>Banned reason:</th><td>'.$row['banned_reason'].'</td></tr>
								<tr><th>Banned date:</th><td>'.Number2Date($row['banned_date']).'</td></tr>						
								<tr><th>BAN HAMMER:</th><td>
								<form method="POST" action="index.php">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unban">
								<input type="hidden" name="id" value="'.$row['id'].'">
								<input type="submit" value="Unban">
								</form>
								</td></tr>';
						}
		echo '
				</tbody></table>
			</div>
	
			<div class="col50right">';

				$result2 = mysqli_query($link, "SELECT details, date FROM log WHERE hacker_id = {$row['id']} AND event = 'staff' and details NOT LIKE 'logged in%' and details NOT LIKE 'duplicate%' ORDER BY DATE DESC");
		echo '
				<table>
					<caption>Staff logs</caption>
					<tbody>';
						if (mysqli_num_rows($result2) == 0)
							echo '<tr><td>No staff logs</td></tr>';
						else	
							while ($row2 = mysqli_fetch_assoc($result2)) 
								echo '<tr><td>'.$row2['details'].' on '.Number2Date($row2['date']).'</td></tr>';
									
		echo '
					</tbody>	
				</table>
				<br><br>';
				
				$result2 = mysqli_query($link, "SELECT ticket.id, ticket.title, ticket.date FROM ticket WHERE hacker_id = {$row['id']} AND respons_id = 0 ORDER BY DATE DESC LIMIT 0, 50");
		echo '
				<table>
					<caption>Tickets</caption>
					<tbody>';
						if (mysqli_num_rows($result2) == 0)
							echo '<tr><td>No tickets</td></tr>';
						else	
							while ($row2 = mysqli_fetch_assoc($result2)) 
								echo '<tr><td>['.$row2['id'].']&nbsp;<a href="?h=doreadticket&ticket_id='.$row2['id'].'">'.$row2['title'].'</a> on '.Number2Date($row2['date']).'</td></tr>';
					
		echo '
					</tbody>	
				</table>
				<br><br>
				<table>
					<caption>Mod-Notes</caption>
					<tbody>
						<tr><td>
							<form method="POST" action="index.php">
								<input type="hidden" name="h" value="domodnote">
								<input type="hidden" name="action" value="post_note">
								<input type="hidden" name="hacker_id" value="'.$row['id'].'">
								<textarea cols="40" rows="6" name="message"></textarea><br>
								<input type="submit" value="Add Note">
							</form>	
						</td></tr>
					</tbody>
				</table>
				<br><br>
				
				<h2>Notes</h2><hr>';
	
				$result2 = mysqli_query($link, "SELECT * FROM modnote WHERE hacker_id = {$row['id']} ORDER BY date DESC");
				if (mysqli_num_rows($result2) == 0) echo "No notes";
				else {
					while ($row2 = mysqli_fetch_assoc($result2)) {
						if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) $delete = '<caption>[<a href="?h=domodnote&action=delete_note&note_id='.$row2['id'].'&hacker_id='.$row['id'].'">Delete Note</a>]</caption>';
						else $delete = '';
						echo '<table width="100%">'.$delete.'
								<tbody>
									<tr><td>'.ShowHackerAlias($row2['creator_id'], 0, false).' replied on '.Number2Date($row2['date']).'<br><br>'.replaceBBC($row2['message']).'<br></td></tr>
								</tbody>
							</table><br>';	
					}		
				}		
		echo '
			</div>';
	}	
?>