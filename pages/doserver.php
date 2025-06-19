<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$server_id =0;
	if (!empty($_GET['id'])) $server_id = intval($_GET['id']);
	if (!empty($_POST['id'])) $server_id = intval($_POST['id']); 
	
	$action = "";
	if (!empty($_GET['action'])) $action = sql($_GET['action']);
	if (!empty($_POST['action'])) $action = sql($_POST['action']);

	$result = mysqli_query($link, "SELECT * FROM server WHERE id = ".$server_id);
	if (mysqli_num_rows($result) == 0)
		return "Server not found.";
	else
		$servername = GetServerName ($server_id);
		
	$row = mysqli_fetch_assoc ($result);
	if ($action != "buy" && $row['hacker_id'] != $hackerdata['id'])
		return "This is not your server.";
	
	if ($action == "sell") {
		// can you drop it yet?
		if ($now <= $row['drop_date'])
			return "This server can not yet be sold because it's currently under federal investigation due to reports of hacker activity.<br><br>They are releasing the server in ".Seconds2Time(SecondsDiff($now, $row['drop_date']));

		// is it a gateway?
		if ($row['gateway'] == 1)
			 return "This is a gateway and can not be dropped. There are better ways to kill your clan.";
		
		$price = intval(((($server_price / 100) * $server_refund) / 100) * $row['efficiency']);
		DropServer ($server_id, $hackerdata['id'], "Sold");
		BankTransfer($hackerdata['id'], "hacker", $price, "Server refund");
		PrintMessage ("Success", "You sold the server and got a refund.");
	}
	
	if ($action == "reset_profit") {
		$result = mysqli_query($link, "UPDATE server SET profit = 0 WHERE hacker_id = {$hackerdata['id']} AND id = $server_id");
		PrintMessage ("Success", "Profit reset on $servername");
	}	

	if ($action == "buy") {
		$_SESSION['dobuyserver'] = 0; // anti refresh
		// is this server free?
		$result = mysqli_query($link, "SELECT id FROM server WHERE hacker_id = 0 AND id = $server_id");
		if (mysqli_num_rows($result) == 0) 
			return "This server is owned by someone. You can not buy it.";

		echo '
			<table width="40%">
				<caption>Buy server #'.$row['id'].'</caption>
				<tbody>
				<form method="POST" action="index.php" name="hf_form">
					<input type="hidden" name="h" value="dobuyserver">
					<input type="hidden" name="server_id" value="'.$row['id'].'">
					<tr><th>Price</td><th>'.$currency.number_format($server_price).'</td></tr>
					<tr><th>New password</th><td><INPUT TYPE="password" NAME="password" maxlength="20"></td></tr>
					<tr><td colspan="2"><input type="submit" value="Buy now"></td></tr>
				</tbody>
			</table>
		';
		echo '<script type="text/javascript">document.hf_form.password.focus();</script>';
		return 1; // do not show the servermanager	
	}
	
	if ($action == "update") {
		// PASSWORD
		$password = "";
		$passupdate = false;
		
		if (!empty($_POST['password'])) {
			$password = sql($_POST['password']);
		
			if ($password != $row['password']) {
				// are you allowed to change?
				if ($row['pass_date'] > $now) return "You have to wait ".SecondsDiff($now,$row['pass_date'])." seconds before you can change this servers password";

				// is the password valid??
				if (!isvalidpassword($password)) return "This password contains illegal characters. Alpha Numeric passwords only!";
				if (strlen($password) < 3 || strlen($password) > 20) return "Password length is incorrect. Minimal 3 characters, maximum of 20 characters.<br>";
				
				$passupdate = true; // all clear			
			}
		}	
		
		// NAME
		$ftp_title = "";
		$ftp_pass = "";
		$ftp_motd = "";
		$ftp_password = "";
		$nameupdate = false;
		$motdupdate = false;
		$ftppassupdate = false;
		$ftp_public = 0;
		
		if ($row['product_id'] == 35) {
			// title
			if (!empty($_POST['ftp_title'])) {
				$ftp_title = sql($_POST['ftp_title']);
				// is the servername valid??
				if (!isvalidfilename($ftp_title)) return "This servername contains illegal characters.";
				if (strlen($ftp_title) < 5 || strlen($ftp_title) > 25) return "servername length is incorrect. Minimal 5 characters, maximum of 25 characters.<br>";
				if ($ftp_title != $row['ftp_title']) $nameupdate = true;
			}
			// motd
			if (!empty($_POST['ftp_motd'])) {
				$ftp_motd = sql($_POST['ftp_motd']);
				// is the motd valid??
				if (strlen($ftp_motd) < 5 || strlen($ftp_motd) > 100) return "MOTD length is incorrect. Minimal 5 characters, maximum of 100 characters.<br>";
				if ($ftp_motd != $row['ftp_motd']) $motdupdate = true;
			}
			if (!empty($_POST['ftp_password'])) {
				$ftp_password = sql($_POST['ftp_password']);
				if (!isvalidpassword($ftp_password)) return "This FTP password contains illegal characters. Alpha Numeric passwords only!";
				if (strlen($ftp_password) < 3 || strlen($ftp_password) > 20) return "FTP Password length is incorrect. Minimal 3 characters, maximum of 20 characters.<br>";
				if ($ftp_password != $row['ftp_password']) $ftppassupdate = true;
			}
			// PUBLIC
			if (!empty($_POST['ftp_public'])) $ftp_public = CheckBox(sql($_POST['ftp_public']));
		}
		
/*		$cloak_color = '';
		$cloak_till = '';
		$cloakupdate = false;
		
		if ($row['product_id'] == 82) {
			if (!empty($_POST['cloak_color'])) {
				$cloak_color = sql($_POST['cloak_color']);
				$valid = true;
				if (strlen($cloak_color) != 6)  $cloak_color = ''; 
				else if (!isHex($cloak_color))  $cloak_color = '';
				if ($cloak_color != '') {
					$cloak_till = date($date_format, strtotime("+".$cloaked_time." hours"));
					$cloakupdate = true;
				}	
			}
		}*/		
		$result = mysqli_query($link, "UPDATE server SET ftp_title = '$ftp_title', ftp_password = '$ftp_password', ftp_motd = '$ftp_motd', ftp_public = $ftp_public WHERE id = ".$server_id);
		PrintMessage ("Success", "Changes made on server $servername");
		if ($passupdate) {
			// can't change password for xx minutes
			$pass_date = date($date_format, strtotime($server_passsuccess_interval));
			$result = mysqli_query($link, "UPDATE server SET pass_date = '".$pass_date."', password = '".$password."' WHERE id = ".$server_id);
			AddLog($server_id, "server", "", "SSH password changed from {$hackerdata['ip']}", $now);
		}	
		if ($nameupdate) AddLog($server_id, "server", "", "Hostname changed from {$hackerdata['ip']}", $now);
		if ($motdupdate) AddLog($server_id, "server", "", "FTP MOTD changed from {$hackerdata['ip']}", $now);
		if ($cloakupdate) AddLog($server_id, "server", "", "Cloaking color set to $cloak_color from {$hackerdata['ip']}", $now);
		if ($ftppassupdate) AddLog($server_id, "server", "", "FTP Password password changed from {$hackerdata['ip']}", $now);
	}
	
	include ("pages/servermanager.php");
?>