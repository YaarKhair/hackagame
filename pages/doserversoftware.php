<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['doserversoftware'] != 1) return "Session error";
	$_SESSION['doserversoftware'] = 0;
	
	$server_id = 0;
	if (!empty($_POST['server_id'])) $server_id = intval($_POST['server_id']);

	$product_code = ""; // software id
	if (!empty($_POST['product_code'])) $product_code = sql($_POST['product_code']); 
	
	// server info
	$result = mysqli_query($link, "SELECT hacker_id, gateway FROM server WHERE id = $server_id");
	$row = mysqli_fetch_assoc($result);
	if ($row['hacker_id'] != $hackerdata['id']) return "This is not your server.";
	if ($row['gateway'] == 1 && (substr($product_code, 0, 7) != "GATEWAY" && $product_code != "SERVERFIREWALL" && substr($product_code, -7) != "SCANNER")) return "You can only install Gateway software on this gateway.";
	if ($row['gateway'] == 0 && substr($product_code, 0, 7) == "GATEWAY") return "You can only install Gateway software a gateway.";

	// do you have the software you want to install in your software library?
	if (!HasOnHDD($hackerdata['id'], $product_code)) return "The software you are trying to install is not on your HDD.";
	
	// read product details
	$result = mysqli_query($link, "SELECT product.id, product.price, product.efficiency, product.level, product.title FROM inventory LEFT JOIN product ON inventory.product_id = product.id WHERE inventory.server_id = 0 AND product.code = '".$product_code."' AND inventory.hacker_id = ".$hackerdata['id']);
	$row = mysqli_fetch_assoc($result); // product details

	// does your level allow you to use it?
	if ($row['level'] > EP2Level(GetHackerEP($hackerdata['id']))) return "Your level does not permit you to install this package.";
	
	$field_value = $row['efficiency'];
	$sql = ', product_id = '.$row['id'];
	$field_name = "";
	$scanner = false;
	if (substr($product_code, 0, 7) == "GATEWAY") { $field_name = "product_efficiency";  } // gw software
	if ($product_code == "SERVERSPAM") { $field_name = "product_efficiency";  } // spam software
	if ($product_code == "SERVERPHISHING") { $field_name = "product_efficiency"; } // phishing
	if ($product_code == "SERVERPORN") { $field_name = "product_efficiency"; } // porn
	if ($product_code == "SERVERSHARING") { $field_name = "product_efficiency"; } // file sharing
	if ($product_code == "SERVERFTP") { $field_name = "product_efficiency"; } // pay-per-download
	if ($product_code == "SERVERFIREWALL") { $field_name = "firewall"; $sql = ''; } // firewall
	if ($product_code == "SERVERCAREPACK") { $field_name = "efficiency"; $sql = '';  } // carepack
	if ($product_code == "SERVERSCANNER") { $scanner = true;  } // spreading infection cleaner
	if ($product_code == "SERVERHONEY") { $field_name = "product_efficiency";  } // cloaker

	// substract from inventory
	if ($field_name != "" || $scanner) {
		if (!DeleteFromInventory($hackerdata['id'], $row['id'])) { echo 'Something went very FUBAR!'; die; }	
		// install on server
		if ($field_name != "") $result = mysqli_query($link, "UPDATE server SET ".$field_name." = ".$field_value.$sql.", profit = profit - ".$row['price']." WHERE id = ".$server_id);
		// clean all infections
		//if ($product_code == "SERVERSCANNER")  CleanServer ($server_id); // removes all spreading infections
		// give Success.
		PrintMessage ("Success", $row['title']." was executed on ".GetServerName ($server_id), "40%");
		AddLog($server_id, "server", "", "{$row['title']} executed from {$hackerdata['ip']}", $now);
	}
	else PrintMessage ("Error", "This software can not be executed on a server.", "40%");
	include ("pages/servermanager.php");
?>
