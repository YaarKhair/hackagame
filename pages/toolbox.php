<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<script type="text/javascript" src="modules/jscolor/jscolor.js"></script>
<?php
	if ($hackerdata['network_id'] != 2) return mysqli_get_value("name", "network", "id", 1)." does not provide access to the world wide web. It's a closed network for n00bs like yourself.";
	
	$action = "";
	if(isset($_POST['action'])) $action = $_POST['action'];
	
	if ($action == "pingip") {
		
		$ip = sql($_POST['ip']); // ip or hostname of target
		$response = "";
		
		if ($_POST['entity'] == "system")
		{
			$result = mysqli_query($link, "SELECT alias FROM hacker WHERE ip = '$ip'");
			if (!$result || mysqli_num_rows($result) == 0) 
			{
				$response = "Pinging $ip [$ip] with 32 bytes of data:<br>";
				$response .= "Time-out<br>Time-out<br>Time-out<br>";
			}
			else
			{
				$row = mysqli_fetch_assoc ($result);
				$response = "Pinging {$row['alias']}.pc [$ip] with 32 bytes of data:<br>";
				$response .= "Reply from $ip in ".mt_rand(10, 40)." ms<br>Reply from $ip in ".mt_rand(10, 40)." ms<br>Reply from $ip in ".mt_rand(10, 40)." ms<br>";
			}
		}
		else
		{
			// convert a hostname to an IP
			$extra_sql = '';
			$pos = strrpos($ip, "-");
			if ($pos !== false) $ip = Hostname2IP($ip);
			// ping the IP		
			$result = mysqli_query($link, "SELECT server.id FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE server.ip = '$ip' AND hacker_id > 0".$extra_sql);
			if (!$result || mysqli_num_rows($result) == 0) 
			{
				$response = "Pinging $ip [$ip] with 32 bytes of data:<br>";
				$response .= "Time-out<br>Time-out<br>Time-out<br>";
			}
			else
			{
				$row = mysqli_fetch_assoc ($result);
				$response = "Pinging ".GetServerName($row['id'])." [$ip] with 32 bytes of data:<br>";
				$response .= "Reply from $ip in ".mt_rand(10, 40)." ms<br>Reply from $ip in ".mt_rand(10, 40)." ms<br>Reply from $ip in ".mt_rand(10, 40)." ms<br>";
			}
		}
		$response .= "<br>Ping complete.";
		PrintMessage("info", $response);
	}	
	
	if ($action == "pingfbi") {
		$ip = sql($_POST['ip']);
		$response = "Pinging ".Alias4Logs($fbi_serverid, "server")." on IP $ip<br><br>";
		if ($ip != $hackerdata['fbi_serverip']) $response .= "Time-out<br>Time-out<br>Time-out<br>";
		else $response .= "Reply from $ip in ".mt_rand(10, 40)." ms<br>Reply from $ip in ".mt_rand(10, 40)." ms<br>Reply from $ip in ".mt_rand(10, 40)." ms<br>";
		$response .= "<br>Ping complete.<br><hr>";
		echo $response;
	}
		
?>
<div class="row">
		<div class="accordion">
        	<input id="ping_tools" type="checkbox" class="accordion-toggle" checked>
            <label for="ping_tools">Ping Tools</label>
            <div class="accordion-box">
				<h3>Ping System</h3>
				<FORM NAME="tool0" ACTION="index.php" METHOD="POST" class="light-bg">
					<INPUT TYPE="hidden" NAME="h" VALUE="toolbox">
					<INPUT TYPE="hidden" NAME="action" VALUE="pingip">
					<INPUT TYPE="hidden" NAME="entity" VALUE="system">
					<div class="row mv5">
						<div class="col w20">IP:</div>
						<div class="col w80"><input type="text" name="ip"></div>
					</div>	
					<div class="row mv5">
						<input type="submit" value="Ping">
					</div>
				</form><br>
				<h3>Ping Server</h3>
				<FORM NAME="tool1" ACTION="index.php" METHOD="POST" class="light-bg">
					<INPUT TYPE="hidden" NAME="h" VALUE="toolbox">
					<INPUT TYPE="hidden" NAME="action" VALUE="pingip">
					<INPUT TYPE="hidden" NAME="entity" VALUE="server">
					<div class="row mv5">
						<div class="col w20">IP/Hostname:</div>
						<div class="co w80"><input type="text" name="ip"></div>
					</div>
					<div class="row mv5">
						<input type="submit" value="Ping">
					</div>
				</form><br>
				<h3>Ping FBI Server</h3>
				<FORM NAME="tool6" ACTION="index.php" METHOD="POST" class="light-bg">
					<INPUT TYPE="hidden" NAME="h" VALUE="toolbox">
					<INPUT TYPE="hidden" NAME="action" VALUE="pingfbi">
					<div class="row mv5">
						<div class="col w20">IP:</div>
						<div class="col w80"><input type="text" name="ip"></div>
					</div>
					<div class="row mv5">
						<input type="submit" value="Ping">
					</div>
				</form>	
			</div>
		</div>
</div>