<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	if (!empty($_POST['action'])) {
		$action = sql($_POST['action']);
		$attacker = sql($_POST['attacker']);
		
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$attacker'");
		$row = mysqli_fetch_assoc($result);
		$attacker_id = intval(sql($row['id']));
		
		if ($action == "pvp") {
			$victim = sql($_POST['victim']);
			$result = mysqli_query($link, "SELECT id FROM hacker WHERE alias = '$victim'");
			if(mysqli_num_rows($result) == 0) return "Victim not found.";
			$row = mysqli_fetch_assoc($result);
			$victimid = $row['id'];
			$chance = BattleSysPvP($attacker_id, $victimid, 0, true);
			$ep_win = GainEP($chance);
			if ($ep_win < 0) $ep_win = 0;
			$skill_win = round($ep_win / 5);
			$ep_fail = round($ep_win / 10);
			$skill_fail = round($skill_win / 10);
			
		}			
		if ($action == "pvs") {
			$server_ip = sql($_POST['server_ip']);
			$result = mysqli_query($link, "SELECT server.id, server.hacker_id, hacker.alias FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE server.ip = '$server_ip'");
			if(mysqli_num_rows($result) == 0) return "Attacker not found.";
			$row = mysqli_fetch_assoc($result);
			$victim = "Server IP ".$server_ip." owned by ".$row['alias'];
			
			$chance = BattleSysPvS($attacker_id, $row['id'], 0, true);
			$ep_win = GainEP($chance);
			if ($ep_win < 0) $ep_win = 0;
			$skill_win = round($ep_win / 5);
			$ep_fail = round($ep_win / 10);
			$skill_fail = round($skill_win / 10);
		}			
		if ($action == "pvf") {
			$victim = "FBI Database";
			$chance = BattleSysPvF($attacker_id);
			$ep_win = GainEP($chance);
			$skill_win = round($ep_win / 5);
			$ep_fail = round($ep_win / 10);
			$skill_fail = round($skill_win / 10);
		}
		$message = "Attacker: $attacker<br>Victim: $victim<br>Chance of successfully winning the battle: $chance%<br><br>If the battle is won:<br>EP: $ep_win / SKILL: $skill_win<br><br>If the battle is lost:<br>EP: $ep_fail / SKILL: $skill_fail";
		PrintMessage("info", $message);
	}
?>
	<table width="60%">
		<caption>BattleSys PvP</caption>
		<tbody>
		<form method="POST" action="index.php" name="hf_form">
			<input type="hidden" name="h" value="modbattlesys">
			<input type="hidden" name="action" value="pvp">
			<tr><td><img src="images/battlesys.jpg" align="right">Attacker: <input type="text" name="attacker"><br>
			Victim: <input type="text" name="victim"><br>
			<br>
			<br><input type="submit" value="Battle!"></td></tr>
		</form>
		</tbody>
	</table>			
	<br><br>
	<table width="60%">
		<caption>BattleSys PvS</caption>
		<tbody>
		<form method="POST" action="index.php" name="hf_form">
			<input type="hidden" name="h" value="modbattlesys">
			<input type="hidden" name="action" value="pvs">
			<tr><td><img src="images/battlesys.jpg" align="right">Attacker: <input type="text" name="attacker"><br>
			Server IP: <input type="text" name="server_ip"><br>
			<br>
			<br><input type="submit" value="Battle!"></td></tr>
		</form>
		</tbody>
	</table>	
	<br><br>
	<table width="60%">
		<caption>BattleSys PvF</caption>
		<tbody>
		<form method="POST" action="index.php" name="hf_form">
			<input type="hidden" name="h" value="modbattlesys">
			<input type="hidden" name="action" value="pvf">
			<tr><td><img src="images/fbi_small.png" align="right">Attacker: <input type="text" name="attacker"><br>
			<br><input type="submit" value="Battle!"></td></tr>
		</form>
		</tbody>
	</table>		
<script type="text/javascript">document.hf_form.attacker.focus();</script>
