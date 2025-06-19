<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$error = "";
	
	if ($hackerdata['clan_id'] == 0) $error = "You are not in a clan.";
	if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "Your gateway is currently offline.";
	
	if ($error == "") {
		
		// the council
		$clan_alias = mysqli_get_value("alias", "clan", "id", $hackerdata['clan_id']);
		$clan_founderid = mysqli_get_value("founder_id", "clan", "id", $hackerdata['clan_id']);
		
		// Edit by 100P:
		// This is included in promote/demote/kick/founder links to fix the popup problem.
		$dirtyfix = 'class="bg-red" style="background-color: transparent !important;"';
		
		echo '<h2>Council Members</h2>
				<div class="row th light-bg">
					<div class="col w20">Hacker</div>
					<!--<div class="col w10">Level</div>
					<div class="col w10">Skill</div>//-->
					<div class="col w10">System</div>
					<div class="col w15">IP</div>
					<div class="col w15">Last Active</div>
					<div class="col w20">Bank</div>
					<div class="col w10">Servers</div>
					<div class="col w10">Action</div>
				</div>
				<div class="dark-bg">';
		
		$result = mysqli_query($link, "SELECT hacker.id, hacker.last_click, hacker.ip, hacker.bankaccount, Count(server.id) AS serverCount FROM hacker LEFT JOIN server ON hacker.id = server.hacker_id WHERE hacker.clan_id = ".$hackerdata['clan_id']." AND hacker.clan_council = 1 GROUP BY hacker.id, hacker.alias ORDER BY last_click DESC");
		while ($row = mysqli_fetch_assoc($result)) {
			
			// council
			if ($hackerdata['clan_council'] == 1) {
				$bankaccount = number_format($row['bankaccount']);
				$options = '[<a href="index.php?h=domember&action=demote&code='.sha1($hackerdata['started'].$hackerdata['last_login']).'&id='.$row['id'].'" '.$dirtyfix.' title="Are you sure you want to demote this member?">demote</a>]';
			}
			else {
				$bankaccount = "**HIDDEN**";
				$options = "N/A";
			}
			
			// only founder can appoint a new founder
			if ($hackerdata['id'] == $clan_founderid && $row['id'] != $clan_founderid) { 
				$options .= '<br>[<a href="index.php?h=domember&action=founder&code='.sha1($hackerdata['started'].$hackerdata['last_login']).'&id='.$row['id'].'" '.$dirtyfix.' title="Are you sure you want to appoint this member as leader?">appoint as leader</a>]';
			}
						
			echo '<div class="row hr-light"><div class="col w20">'.ShowHackerAlias ($row['id'], 0, false, true, true, false, false).'</div>';
			//echo '<div class="col w10">'.EP2Level(GetHackerEP($row['id'])).'</div>';
			//echo '<div class="col w10">'.GetSkill($row['id']).'</div>';
			echo '<div class="col w10">'.GetStatus($row['id']).'</div>';
			echo '<div class="col w15">'.$row['ip'].'</div>';
			echo '<div class="col w15">'.Number2Date($row['last_click']).'</div>';
			echo '<div class="col w20">'.$bankaccount.'</div>';
			echo '<div class="col w10">'.$row['serverCount'].'</div>';
			echo '<div class="col w10">'.$options.'</div></div>';
		}
		echo '</div><br><br>';
		
		// the members
		echo '
			<h2>Members</h2>
				<div class="row th light-bg">
					<div class="col w20">Hacker</div>
					<!--<div class="col w10">Level</div>
					<div class="col w10">Skill</div>//-->
					<div class="col w10">System</div>
					<div class="col w15">IP</div>
					<div class="col w15">Last Active</div>
					<div class="col w20">Bank</div>
					<div class="col w10">Servers</div>
					<div class="col w10">Action</div>
				</div>
				<div class="dark-bg">';
		
		$result = mysqli_query($link, "SELECT hacker.id, hacker.ip, hacker.last_click, hacker.bankaccount, Count(server.id) AS serverCount FROM hacker LEFT JOIN server ON hacker.id = server.hacker_id WHERE hacker.clan_id = ".$hackerdata['clan_id']." AND hacker.clan_council = 0 GROUP BY hacker.id, hacker.alias ORDER BY last_click DESC");
		if (mysqli_num_rows($result) == 0) echo '<tr><td colspan="8">No members.</div></tr>';
		else {
			$color = 0;
			while ($row = mysqli_fetch_assoc($result)) {
				
				if ($hackerdata['clan_council'] == 1) {
					$bankaccount = number_format($row['bankaccount']);
					$options = '[<a href="index.php?h=domember&action=promote&code='.sha1($hackerdata['started'].$hackerdata['last_login']).'&id='.$row['id'].'" '.$dirtyfix.' title="Are you sure you want to promote this member?">promote</a>]<br>[<a href="index.php?h=domember&action=kick&code='.sha1($hackerdata['started'].$hackerdata['last_login']).'&id='.$row['id'].'" '.$dirtyfix.' title="Are you sure you want to kick this member?">kick</a>]';
				}	
				else {
					$bankaccount = "**HIDDEN**";
					$options = "N/A";
				}	
				echo '<div class="row hr-light"><div class="col w20">'.ShowHackerAlias ($row['id'], 0, false, true, true, false, false).'</div>';
				//echo '<div class="col w10">'.EP2Level(GetHackerEP($row['id'])).'</div>';
				//echo '<div class="col w10">'.GetSkill($row['id']).'</div>';
				echo '<div class="col w10">'.GetStatus($row['id']).'</div>';
				echo '<div class="col w15">'.$row['ip'].'</div>';
				echo '<div class="col w15">'.Number2Date($row['last_click']).'</div>';
				echo '<div class="col w20">'.$bankaccount.'</div>';
				echo '<div class="col w10">'.$row['serverCount'].'</div>';
				echo '<div class="col w10">'.$options.'</div></div>';
			}
		}
			echo "</div>";
	}
	else {
		PrintMessage ("Error", $error, "40%");
	}
?>