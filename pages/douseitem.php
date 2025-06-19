<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['loaded'] != 0) return "Session error";
	$_SESSION['loaded'] = 1;
	
	$inventory_id = 0;
	if (!empty($_GET['inventory_id'])) $inventory_id = intval($_GET['inventory_id']);
	
	// you have this on your HDD?
	$query = "SELECT inventory.product_id FROM inventory WHERE server_id = 0 AND hacker_id = ".$hackerdata['id']." AND id = ".$inventory_id;
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) {
		return "You don't have this file on your HDD!";
		AddLog ($hackerdata['id'], "hacker", "abuse", "Tried to install/use a file, which was not on his HDD", $now);
	}
	else {
		$row = mysqli_fetch_assoc($result);
		$product_id = $row['product_id'];
	}	
	
	// xmas stuff
	if ($product_id == 101) {
		$result = mysqli_query ($link, "UPDATE hacker SET snow = 1");
		// insert news
		$result = mysqli_query($link, "SELECT id FROM topic WHERE title LIKE 'News' AND clan_id = 0 AND board_id = 0 AND post_id = 0");
		if (mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$message = "{$hackerdata['alias']} won the XMAS 2013 challenge first!<br><br>He found and executed the secret XMAS file and made it snow for everyone on HF!!!!!<br><br>{$hackerdata['alias']} gets 50HP reward!";
			$result = mysqli_query($link, "INSERT INTO topic (board_id, post_id, clan_id, hacker_id, date, title, message, lastpost_date, lastpost_hackerid) VALUES ({$row['id']}, 0, 0, $ibot_id, '$now', 'SNOW!', '$message', '$now', $ibot_id)");
			// update last post data for current board
			$result = mysqli_query($link, "UPDATE topic SET lastpost_date = '$now', lastpost_hackerid = $ibot_id WHERE post_id = 0 AND board_id = 0 AND id = {$row['id']}");
		}	
		AddHackpoint ($hackerdata['id'], 0, 50, "You won the XMAS 2013 challenge");
		DeleteFromInventory($hackerdata['id'], $product_id);
		Return "You won the XMAS 2013 challenge!!!!";
	}
	
	// get product info
	$query = "SELECT level, can_install, efficiency, code FROM product WHERE id = ".$product_id;
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);
	
	if (!AllowedUseProduct($hackerdata['id'], $product_id)) return "Your level does not permit you to install this package.";	
	if ($row['can_install'] != 1) return "You can not install this on your computer."; 
	
	// use or install the product
	$result2 = mysqli_query($link, "SELECT system.id FROM system INNER JOIN product ON system.product_id = product.id WHERE system.hacker_id = ".$hackerdata['id']." AND product.code = '".$row['code']."'");
	
	// EXISTING
	if (mysqli_num_rows($result2) > 0) {
		// reinstall
		$row2 = mysqli_fetch_assoc ($result2);
		$result2 = mysqli_query($link, "UPDATE system SET product_id = $product_id, efficiency = ".$row['efficiency']." WHERE id = ".$row2['id']);
	}
	// NEW
	else
		// this patch will not be found on your system, so thats why this code is in the 'new' section of the code. it will however update something existing
		if ($row['code'] == "DEFRAG") {
			// system.id, and initial efficiency of the HDD?
			$result2 = mysqli_query($link, "SELECT system.id, product.efficiency FROM system LEFT JOIN product on system.product_id = product.id WHERE system.hacker_id = {$hackerdata['id']} AND product.code = 'HDD'");
			if (mysqli_num_rows($result2) == 0) return "You do not have an HDD installed."; // not possible because you are installing FROM your HDD, but hey, since were already checking the system.id, why not check it.
			$row2 = mysqli_fetch_assoc ($result2);
			$result2 = mysqli_query($link, "UPDATE system SET efficiency = ".$row2['efficiency']." WHERE id = ".$row2['id']);
		}	
		// install new
		else $result2 = mysqli_query($link, "INSERT INTO system (hacker_id, product_id, efficiency) VALUES (".$hackerdata['id'].", $product_id, ".$row['efficiency'].")");	

	// substract from inventory
	DeleteFromInventory($hackerdata['id'], $product_id);
	PrintMessage ("Success", "Item succesfully installed.", "40%");
	include ("pages/software.php");
?>