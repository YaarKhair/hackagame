<?php
	// reset the PrivateBay FTP servers
	$result = mysqli_query($link, "SELECT id FROM server WHERE ftp_title LIKE 'PrivateBay%'");
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			if (WillItWork($privatebay_chance)) {
				
				$server_id = $row['id'];
				$random_ip = randomip();
				$random_pass = createrandomPassword();
				
				$result2 = mysqli_query ($link, "UPDATE server SET ip = '$random_ip', ftp_password = '$random_pass' WHERE id = $server_id"); // kills all current transfers
				$result2 = mysqli_query ($link, "SELECT id FROM inventory WHERE server_id = $server_id"); // see how many files are on the server
				
				$num_files = $privatebay_numfiles - mysqli_num_rows($result2);

				if ($num_files > 0) {
					for ($i = 0; $i < $num_files; $i++) {
						$type = mt_rand(1,2);
						$product_id = 0;
						$price = mt_rand(500,1000); // will be bought for 2000-5000 below
						if ($type == 1) $product_id = mysqli_get_value("id", "product", "code", "TRADEMOVIES", false);
						if ($type == 2) $product_id = mysqli_get_value("id", "product", "code", "TRADESOFTWARE", false);
						if ($product_id > 0) $result2 = mysqli_query($link, "INSERT INTO inventory (hacker_id, product_id, server_id, price) VALUES ($ibot_id, $product_id, $server_id, $price)");
					}
				}
			}
		}
	}	
?>