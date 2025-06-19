<?php
	// Kick gateways with less than 3 servers in the clan offline
	$result = mysqli_query ($link, "SELECT id FROM clan WHERE active = 1");
	while($row = mysqli_fetch_assoc($result)) 
	{
		$gw_result = mysqli_query($link, "SELECT COUNT( server.id ) AS numservers FROM server LEFT JOIN hacker ON server.hacker_id = hacker.id WHERE hacker.clan_id = {$row['id']} AND server.gateway = 0");
		while($gw_row = mysqli_fetch_assoc($gw_result)) 
		{
			$gw_id = GetGateway($row['id']);
			$offline_till = mysqli_get_value("offline_till", "server", "id", $gw_id);
			if($gw_row['numservers'] < $gateway_servercount && $offline_till != '99999999999999') offlineServer($gw_id);	// Kick offline if not already offline and less than 3 servers
			if($gw_row['numservers'] >= $gateway_servercount && $offline_till == '99999999999999') onlineServer($gw_id);  // Get online if the reason you were offline is the server count
		}
	}
?>