<?php
	$result = mysqli_query($link, "SELECT infection.*, product.title FROM infection LEFT JOIN product ON infection.product_id = product.id WHERE infection.hacker_id = {$hackerdata['id']} AND infection.spreading = 0");
	
	echo '
			<h1>Infections</h1>
				<div class="row th light-bg">
					<div class="col w15">Target</div>
					<div class="col w15">IP</div>
					<div class="col w20">Virus</div>
					<div class="col w15">Infection date</div>
					<div class="col w20">Status</div>
					<div class="col w15">Action</div>
				</div>
			';	
	if (!$result || mysqli_num_rows($result) == 0) echo '<div class="row">'.PrintMessage("info", "You have no infections.").'</div>';
	else {
		echo '<div class="dark-bg">';
		while ($row = mysqli_fetch_assoc($result)) {
			$virus = $row['title'];
			$date = Number2Date($row['date']);
			//$code = mysqli_get_value ("code", "product", "id", $row['product_id']);
			$ip = $row['victim_ip']; // static. this is the ip when the hack was initiated
			$entity = $row['victim_entity'];
			
			//if ($code == "SERVERHACK" || $code == "GWDESTROYER") $entity = "server";
			//else $entity = "hacker";
			
			// infection date in the future, or a succesful hack is not yet set to READY by infection
			if ($row['ready'] == 0) {
				$status = "Attempting to install virus";
				$target = Alias4Logs ($row['victim_id'], $entity, true);
				$action = "";
			}
			else {
				// infection date in the past and set to ready
				if ($row['success'] == 1) {
					$status = "Infected";
					$target = Alias4Logs ($row['victim_id'], $entity, true);
					$action = '<form class="small alt-design" action="index.php"><input type="hidden" name="h" value="dovirus"><input type="hidden" name="action" value="uninstall"><input type="hidden" name="infection_id" value="'.$row['id'].'"><input type="submit" class="bg-red w100i" value="Uninstall" disabled></form><form class="small alt-design" action="index.php"><input type="hidden" name="h" value="dovirus"><input type="hidden" name="action" value="execute"><input type="hidden" name="infection_id" value="'.$row['id'].'"><input type="submit" class="w100i" value="Execute"></form>';
				}	
				// infection date in the past but failed
				if ($row['success'] == 0) {
					$status = "Failed or connection lost";
					$action = '<form class="small alt-design" action="index.php"><input type="hidden" name="h" value="dovirus"><input type="hidden" name="action" value="removefailed"><input type="hidden" name="infection_id" value="'.$row['id'].'"><input type="submit" class="bg-red w100i" value="Remove" disabled></form>';
					$target = Alias4Logs ($row['victim_id'], $entity, true);
				}	
			}	
			echo "<div class='row mv5 hr-light'><div class='col w15'>$target</div><div class='col w15'>$ip</div><div class='col w20'>$virus</div><div class='col w15'>$date</div><div class='col w20'>$status</div><div class='col w15'>$action</div></div>";
		}	
		echo '</div>';
	}	

	
	// remove all failed
	echo '<form action="index.php"><input type="hidden" name="h" value="dovirus"><input type="hidden" name="action" value="removeallfailed"><input class="bg-red mh10" type="submit" value="Remove all failed infections" disabled></form><form action="index.php"><input type="hidden" name="h" value="dovirus"><input type="hidden" name="action" value="removeallfailed"><input type="submit" value="Uninstall All Infections" class="bg-red" disabled></form>';
?>
	<br><div class="row th light-bg">
		<div class="col w100">Active Spreading Server Infections</div>
	</div>
<?php
	$result = mysqli_query($link, "SELECT infection.product_id, server.id, server.ip, server.efficiency FROM infection LEFT JOIN server ON infection.victim_id = server.id WHERE infection.hacker_id = {$hackerdata['id']} AND infection.spreading = 1");
	$num = mysqli_num_rows($result);
	if ($num > 0) {
		echo '<div class="dark-bg">';
		while ($row = mysqli_fetch_assoc($result)) 
			echo "<div id='row hr-light'>".GetServerName($row['id']).' ['.$row['ip'].'], health '.$row['efficiency'].'%, infected by '.mysqli_get_value("title", "product", "id", $row['product_id']).'</div>';
		echo '</div>';
	} else echo "<div id='row'>".PrintMessage("Info", "You did not infect any servers with any spreading viruses")."</div>";
	
?>