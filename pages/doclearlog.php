<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
		if (!HasInstalled($hackerdata['id'], "ACCESSLOGS")) return "No logging tool installed.";
		
		if (!empty($_POST['log'])) $log = sql($_POST['log']);
		else $log = "";
		
		if ($log != "transfer" && $log != "system" && $log != "server" && $log != "hack") return "Log not found.";
		
		if ($log == "server") {
			if (!empty($_POST['server_id'])) $server_id = intval($_POST['server_id']);
			else $server_id = 0;
			
			// specific server
			if ($server_id > 0) {
				$owner_id = mysqli_get_value ("hacker_id", "server", "id", $server_id);
				if ($owner_id != $hackerdata['id']) return "This is not your server!";
				$result = mysqli_query($link, "UPDATE log SET deleted = 1 WHERE server_id = $server_id AND date <= '$now'");
			}
			// all servers
			else {
				$result = mysqli_query($link, "UPDATE log SET deleted = 1 WHERE server_id IN (SELECT id FROM server WHERE hacker_id = {$hackerdata['id']}) AND date <= '$now'");
			}
		}
		else $result = mysqli_query($link, "UPDATE log SET deleted = 1 WHERE hacker_id = {$hackerdata['id']} AND event = '$log' AND date <= '$now'");
		
		PrintMessage ("Success", "The $log.log was cleared.", "40%");
		if ($log == "server") include ("servermanager.php");
		else include ("logs.php");
?>
