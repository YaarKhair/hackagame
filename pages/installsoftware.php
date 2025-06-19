<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['loaded'] == 0) {
		$_SESSION['loaded'] = 1;
		$server_id = 0;
		if (!empty($_GET['server_id'])) $server_id = intval($_GET['server_id']);

		$product_id = 0; // software id
		if (!empty($_GET['product_id'])) $product_id = intval($_GET['product_id']);
		
		// is it your server?
		$result = mysqli_query($link, "SELECT * FROM `server` WHERE `id` = ".$server_id." AND `hacker_id` = ".$hackerdata['id']);
		if (mysqli_num_rows ($result) == 0) {
			PrintMessage ("error", "This is not your server.");
			AddLog($hackerdata['id'], "hacker", "abuse", "Tried to install software on a server that is not his own.", $now);
		}
		else {
			// do you have the software you want to install in your software library?
			$result = mysqli_query($link, "SELECT inventory.*, product.price, product.efficiency FROM `inventory` LEFT JOIN `product` ON inventory.product_id = product.id WHERE `server_id` = 0 AND `product_id` = ".$product_id." AND `hacker_id` = ".$hackerdata['id']);

			if (mysqli_num_rows ($result) == 0) {
				PrintMessage ("error", "You don't have the software you are trying to install.");
			}
			else {
				$row = mysqli_fetch_assoc($result); // product details
				$efficiency = $row['efficiency'];
				$sql = ', `product_id` = '.$product_id;
				if ($product_id == 9) { $update_table = "product_efficiency";  } // spam software
				if ($product_id == 14) { $update_table = "product_efficiency"; } // phishing
				if ($product_id == 15) { $update_table = "product_efficiency"; } // porn
				if ($product_id == 28) { $update_table = "product_efficiency"; } // file sharing
				if ($product_id == 35) { $update_table = "product_efficiency"; } // pay-per-download
				if ($product_id == 25) { $update_table = "firewall"; $sql = ''; } // firewall
				if ($product_id == 26) { $update_table = "efficiency"; $sql = '';  } // carepack

				// substract from inventory
				if (!DeleteFromInventory($hackerdata['id'], $product_id)) {
					echo 'Something went very FUBAR!'; die;
				}	

				// install on server
				$result = mysqli_query($link, "UPDATE `server` SET `".$update_table."` = ".$efficiency.$sql.", `profit` = `profit` - ".$row['price']." WHERE `id` = ".$server_id);
				
				// give notice.
				PrintMessage ("success", "The software was installed on ".GetServerName ($server_id));
				AddLog($hackerdata['id'], "hacker", "server", "Software installed on server ".GetServerName ($server_id), $now);
			}
		}
	}
	else {
		AddLog ($hackerdata['id'], "hacker", "abuse", "page refresh of ".$page2load, $now);
	}
?>