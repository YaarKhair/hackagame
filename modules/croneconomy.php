<?php
	include ("/var/www/modules/linegraph/linegraph.php");
	$result = mysqli_query($link, "SELECT * FROM economy ORDER BY id DESC LIMIT 20");

	$counter = 0;
	$spam_data = array(); 
	$phishing_data = array(); 
	$porn_data = array(); 
	$counter = mysqli_num_rows($result)-1;
	
	while ($row = mysqli_fetch_assoc($result)) {
		$spam_data[0][$counter] = $row['spam_economy']; 
		$phishing_data[0][$counter] = $row['phishing_economy']; 
		$porn_data[0][$counter] = $row['porn_economy']; 
		$filesharing_data[0][$counter] = $row['filesharing_economy']; 
		$a_h_indexes[$counter] =  $row['day'];
		$counter -= 1;
	}
	
	// Vertical indexes 
	$a_v_indexes[0] = 0; 
	$a_v_indexes[1] = 10; 
	$a_v_indexes[2] = 20; 
	$a_v_indexes[3] = 30; 
	$a_v_indexes[4] = 40; 
	$a_v_indexes[5] = 50; 
	$a_v_indexes[6] = 60; 
	$a_v_indexes[7] = 70; 
	$a_v_indexes[8] = 80; 
	$a_v_indexes[9] = 90; 
	$a_v_indexes[10] = 100; 

	// Colors array 
	$a_colors[0] = "FF0099"; 
	$a_colors[1] = "6600FF"; 
	$a_colors[2] = "FF0099"; 
	$a_colors[3] = "33CC00"; 
	$a_colors[4] = "CCCC00"; 
	$a_colors[5] = "0000CC"; 
	$a_colors[6] = "FFFF66"; 
	$a_colors[7] = "006600"; 
	$a_colors[8] = "00FFFF"; 
	$a_colors[9] = "FFFFFF"; 
	$a_colors[10] = "660033"; 
	$a_colors[11] = "66FFCC"; 
	
	// generate picture
	graphic($spam_data, $a_v_indexes, $a_h_indexes, $a_colors, "economy_spam"); 	
	graphic($phishing_data, $a_v_indexes, $a_h_indexes, $a_colors, "economy_phishing"); 	
	graphic($porn_data, $a_v_indexes, $a_h_indexes, $a_colors, "economy_porn"); 	
	graphic($filesharing_data, $a_v_indexes, $a_h_indexes, $a_colors, "economy_filesharing"); 	
?>
