<?php
	/**
	**	Few stuff before beginning: 
	**	$victim_row has information about the victim
	** 	$infection_row has information about the infection
	**/
	
	/**
	**	Gateway Destroyer
	**	Kicks a gateway offline
	**/
    if ($infection_row['product_id'] == $PRODUCT['Gateway Destroyer']) {
    	
    	// deface the clan page, optionally
    	$extra_info = $infection_row['extra_info']; // defacement text
    	if ($extra_info != "") {
    		$defaced_till = date($date_format, strtotime ("+". $defaced_hours . " hours"));
    		$deface_result = mysqli_query ($link, "UPDATE clan SET extra_info = '$extra_info', defaced_till = '$defaced_till' WHERE id = $victim_clanid");
    	}
        // Kick the gateway offline
        $offline_till = date($date_format, strtotime("+".$gwoffline_afterhack." minutes")); 
        $gateway_offline_result = mysqli_query($link, "UPDATE server SET offline_from = '$now', offline_till = '$offline_till' WHERE id = ".$infection_row['victim_id']);
		
		// Hack details
    $addlog_attacker = "Offline time: $gwoffline_afterhack minutes.<br><br>";
		
		$stolen_iplog = "Target: ".$target;
		$stolen_iplog .= "Gateway IP Log:<br>--------------<br>";
			
		// a list of all members and their current IPs
		$ip_result = mysqli_query ($link, "SELECT alias, ip FROM hacker WHERE clan_id = $victim_clanid");
		while ($ip_row = mysqli_fetch_assoc($ip_result))		
			$stolen_iplog .= "{$ip_row['alias']} connected by IP {$ip_row['ip']}<br>";
		
		SendIM (0, $hackerdata['id'], "Gateway IP Log", $stolen_iplog, $now);
			
        $key = 'gw_ddos_points';	// used in the war system
    }
	
	
	
	/**
	**	Server viruses that spread 
	**	Health Destroyer and Revenue Stealer
	**/
    if ($infection_row['product_id'] == $PRODUCT['Server Revenue Stealer'] || $infection_row['product_id'] == $PRODUCT['Server Health Destroyer'] || $infection_row['product_id'] == $PRODUCT['Server Tracer']) {
	
		// register result for the hacker (achievement thingy)
		RegisterResult($hackerdata['id'], 'serversinfected', $now);
		
    }
	
	/**
	**	Server Software Stealer
	**	Steals whatever software is in the server
	**/
    if($infection_row['product_id'] == $PRODUCT['Server Software Stealer']) {
	
        // Get the software installed on that server and remove it
        $server_id = $infection_row['victim_id'];
        $product_installed = mysqli_get_value("product_id","server","id",$server_id);
           
		// Server has software on it?
        if($product_installed > 0) { 
		
			// Set base variables
            $price = 0; // because it's on your hdd
            $inventory_id = mysqli_next_id("inventory"); // id of the inventory that is going to be inserted
			
			// Insert the software
            $insert_software_result = mysqli_query($link, "INSERT INTO inventory (hacker_id, server_id, file_id, product_id, datechanged, price) VALUES (0, $server_id, 0, $product_installed,  '$now', $price)");                

			// Set the logs
			$product_name = FileInfo($inventory_id, "title");
			$addlog_attacker = "Initiated download of $product_name from $target";
			
			// download time
			$size = FileInfo($product_installed, "size");
			$minutes = DownloadTime($hackerdata['id'], $size);
			$ready_date = date($date_format, strtotime("+".$minutes." minutes"));
			
			// start download (cron)
			$filetransfer_result = mysqli_query($link, "INSERT INTO filetransfer (inventory_id, source_id, source_ip, source_entity, destination_id, destination_ip, destination_entity, ready_date) VALUES (".$inventory_id.", ".$server_id.", '".$ip."', 'server', ".$hackerdata['id'].", '".$hackerdata['ip']."', 'hacker', '".$ready_date."')");
			
			// kill it on the server, even if the transfer will never finish
			$remove_software_result = mysqli_query($link, "UPDATE server SET product_id = 0 WHERE id = $server_id");
			
        } 	else $addlog_attacker = "No software found on server.";
		
	}
    
	/**
	**	Brute Force Password Cracker
	**	Steals the password of a server
	**/
    if ($infection_row['product_id'] == $PRODUCT['Brute Force Password Cracker']) {
		
		// Get the password
        if ($infection_row['victim_id'] == $fbi_serverid) {
			$pass = mysqli_get_value("fbi_serverpass", "hacker", "id", $hackerdata['id']);
            $update_fbi_result = mysqli_query ($link, "UPDATE hacker SET fbi_serverpass_date = '$now' WHERE id = {$hackerdata['id']}"); // so that we can prevent a cron resetting the password seconds after we got the result (cron_1h)
        }
		else $pass = mysqli_get_value("password", "server", "id", $infection_row['victim_id']);	// NOT FBI SERVER

        $addlog_attacker = "Password after decryption: $pass";
    }

	/**
	**	server.log Stealer
	**/
	if($infection_row['product_id'] == $PRODUCT['Server.log stealer']) {
		
		// Which log is it?
		$title = mysqli_get_value("title", "product", "id", $infection_row['product_id']);
		$title = explode(".", $title);
		$log2steal = strtolower($title[0]);	// for example: bank

		// Select the logs
        $select_log_result = mysqli_query($link, "SELECT date, details FROM log WHERE server_id = {$infection_row['victim_id']} AND date <= '$now' AND deleted = 0 ORDER BY date DESC LIMIT $efficiency");
		
		// No logs? Send IM instantly
        if (mysqli_num_rows($select_log_result) == 0) SendIM (0, $hackerdata['id'], "$log2steal.log stealer (0)", "The $log2steal.log of $target appears to be empty.", $now);
        else {
		
			// Build the logs message
			$num = 0;
            $stolenlogs = '';
            while ($log_row = mysqli_fetch_assoc($select_log_result)) {
                if ($log2steal == "bank") {
                    list($amount, $reason) = explode('|', $log_row['details']);
                    $stolenlogs .= "Date: ".Number2Date($log_row['date']).", Amount: ".$amount.", Reason: ".$reason."<br>";
                }
                else { 
					$stolenlogs .= "Date: ".Number2Date($log_row['date']).", Details: ".$log_row['details']."<br>";
					$num ++;
                }
            }
			SendIM (0, $hackerdata['id'], "$log2steal.log stealer (".mysqli_num_rows($select_log_result).")", "$efficiency most recent lines out of the $log2steal.log of $target:<br><br>".$stolenlogs, $now);
			// Hack details
			$addlog_attacker = "$num $log2steal.log lines indexed.";
		}
		
	}
?>