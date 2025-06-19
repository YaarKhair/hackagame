<?php
	/* CRONNED SCANS */
	$hacker_id = 0;
	$prev_hacker_id = 0;
	$message = "";
	$num_scanned_systems = 0;
	$num_scanned_servers = 0;

	// longest query in HF history. copy/paste to phpmyadmin for details. long story short, we sort by hacker_id, which is either the target_id or the owner of the target_id. this way we can send out 1 IM per hacker
	$result = mysqli_query($link, "SELECT cronscans.id, cronscans.target_id, cronscans.target_id as hacker_id, antivirus.entity, cronscans.scan_id, cronscans.refresh_ip FROM cronscans LEFT JOIN antivirus ON cronscans.scan_id = antivirus.id WHERE date < '$now' AND antivirus.entity = 'hacker' Union All SELECT cronscans.id, cronscans.target_id, server.hacker_id, antivirus.entity, cronscans.scan_id, cronscans.refresh_ip FROM cronscans LEFT JOIN antivirus ON cronscans.scan_id = antivirus.id LEFT JOIN server ON cronscans.target_id = server.id WHERE date < '$now' AND antivirus.entity = 'server' ORDER BY hacker_id");
	if (mysqli_num_rows($result) > 0) {
		$num_rows = mysqli_num_rows($result);
		$count_rows = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$count_rows ++;
			// what to scan?
			$target_id = $row['target_id'];
			$entity = $row['entity'];
			$refresh_ip = $row['refresh_ip'];
			$hacker_id = $row['hacker_id'];
			
			if ($entity == "hacker") 
			{
				$log = "system";
				$num_scanned_systems++;
			}
			else 
			{
				$log = "server";
				$num_scanned_servers ++;
				if ($refresh_ip == 1) RefreshServerIP($target_id); // Refresh IP?
			}
			
			CleanSystem($target_id, "Virus Scanner", $entity, 1); // find installed virusess, not pending
			
			if ($prev_hacker_id > 0 || $count_rows == $num_rows)
				if ($hacker_id != $prev_hacker_id || $count_rows == $num_rows)
				{
					SendIM (0, $hacker_id, "Virus Scan result", "We scanned $num_scanned_systems computers and $num_scanned_servers servers. For details view your logs.", $now);
					$num_scanned_systems = 0;
					$num_scanned_servers = 0;
				}
			
			// delete current record
			$result2 = mysqli_query($link, "DELETE FROM cronscans WHERE id = ".$row['id']);
			$prev_hacker_id = $hacker_id; // remember the previous hacker to mass mail the results
		}
	}	
?>