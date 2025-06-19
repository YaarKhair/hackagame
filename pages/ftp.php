<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$_SESSION['loaded'] = 0; // anti refresh
	
	if (!isset($server_ip)) $server_ip = 0;
	if (!empty($_GET['server_ip'])) $server_ip = sql($_GET['server_ip']);
	
	if (!empty($_GET['ftp_pass'])) $_SESSION['ftp_pass'] = sha1($_GET['ftp_pass']); // received a password? save in session
	
	$query = "SELECT server.* FROM server LEFT JOIN product ON server.product_id = product.id WHERE product.code = 'SERVERFTP' AND server.ip = '".$server_ip."'";
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) return "FTP connection failed on port 21.";
	
	$row = mysqli_fetch_assoc($result);

	if ($hackerdata['network_id'] != 2) return "You are connected to ".mysqli_get_value("name", "network", "id", 1).". The function you are trying to use is unavailable from this network.";
	
	// if the server is set to private, you must enter a password first if you didnt already this session.
	$request_pass = false; // show file listing
	if ($row['ftp_public'] == 0 && (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2))) {
		$request_pass = false;
		if (!isset($_SESSION['ftp_pass'])) $request_pass = true; // no password given yet
		else if ($_SESSION['ftp_pass'] != sha1($row['ftp_password'])) $request_pass = true; // wrong password
		
		if ($request_pass) {
			echo '<div align="center"><h2>Enter the password</h2>
					<form method="GET" action="index.php">
						<input type="hidden" name="h" value="ftp">
						<input type="hidden" name="server_ip" value="'.$server_ip.'">
						<input type="password" name="ftp_pass" class="icon pass" placeholder="FTP Password">
						<input type="submit" value="Login">
					</form></div>';
					return "This is a password protected FTP server.";
		}
	}
	
	// upload values
	$server_id = $row['id'];
	$owner_id = $row['hacker_id'];
	$checksum = sha1($row['ip']);
	$dellink = '';
	$title = $row['ftp_title'];
	$motd = $row['ftp_motd'];
	$group_upload = $row['group_upload'];
	$group_download = $row['group_download'];
	$upload_message = '';
	$download_message = '';

	// if you can not upload nor download from a server, you have no businesson the server
	$access_denied = 0;
	if ($group_download != 0 && !InGroup($hackerdata['id'], $group_download)) $access_denied++;
	if ($group_upload != 0 && !InGroup($hackerdata['id'], $group_upload)) $access_denied++;
	if ($access_denied == 2) return "You are not allowed to upload or download from this server.";
	
	if ($group_upload == 0) $upload_message .= 'Everyone';
	else $upload_message .= mysqli_get_value ("name", "permgroup", "id", $group_upload);
	if ($group_download == 0) $download_message .= 'Everyone';
	else $download_message .= mysqli_get_value ("name", "permgroup", "id", $group_download);
	if ($title == "") $title = $row['ip'];
	
	// ftp info
?>
	<div class="accordion">
		<input id="ftp_connect" type="checkbox" class="accordion-toggle" checked>
		<label for="ftp_connect">Connecting to <?php echo $row['ip']; ?></label>
		<div class="accordion-box">
			<div class="row hr-light">
				<div class="col w30">FTP Server</div>
				<div class="col w70"><?php echo $title; ?></div>
			</div>
			<div class="row hr-light">
				<div class="col w30">System Operator</div>
				<div class="col w70"><?php echo ShowHackerAlias($row['hacker_id']); ?></div>
			</div>
			<div class="row hr-light">
				<div class="col w30">Who Can Download?</div>
				<div class="col w70"><?php echo $download_message; ?></div>
			</div>
			<div class="row hr-light">
				<div class="col w30">Who Can Upload?</div>
				<div class="col w70"><?php echo $upload_message; ?></div>
			</div>
			<div class="row hr-light">
				<div class="col w30">Message Of The Day</div>
				<div class="col w70"><?php echo $motd; ?></div>
			</div>
		</div>
	</div>
	
	<h2>Remote File Listing</h2>
	<div class="row th light-bg">
		<div class="col w30">File</div>
		<div class="col w10">Size</div>
		<div class="col w15">Uploader</div>
		<div class="col w20">Uploaded On</div>
		<div class="col w10">Price</div>
		<div class="col w15">Actions</div>
	</div>
	<?php
		// read files on this FTP server	
		$result = mysqli_query($link, "SELECT inventory.* FROM inventory WHERE server_id = ".$row['id']." ORDER BY datechanged DESC");
		if(mysqli_num_rows($result) == 0) echo '<div class="row">'.PrintMessage("Info", "This FTP has no files.")."</div>";
		else {
			echo "<div class='dark-bg'>";
			while($row = mysqli_fetch_assoc($result)) {
				$download_link = "?h=download&inventory_id=".$row['id']."&server_id=".$server_id."&checksum=".$checksum;
				$actions = '<input type="button" value="Download" onclick="document.location = \''.$download_link.'\'">';
				if ($owner_id == $hackerdata['id'] || $row['hacker_id'] == $hackerdata['id']) $actions .= '<input value="Delete" type="button" class="bg-red" onclick="document.location = \'?h=dodeletefile&id='.$row['id'].'\'">';
				echo "<div class='row hr-light'>";
				echo "<div class='col w30'>".FileInfo($row['id'], "title")."</div>";
				echo "<div class='col w10'>".DisplaySize(FileInfo($row['id'], "size"))."</div>";
				echo "<div class='col w15'>".ShowHackerAlias($row['hacker_id'], 1)."</div>";
				echo "<div class='col w20'>".Number2Date($row['datechanged'])."</div>";
				echo "<div class='col w10'>".$currency.number_format($row['price'])."</div>";
				echo "<div class='col w15'><form class='small'>$actions</form></div>";
				echo "</div>";
			}
			echo "</div>";
		}
	?>
	<br>
	<?php if ($group_upload == 0 || InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], $group_upload)) { ?>
		<h2>Local File Listing</h2>
		<div class="row th">
			<div class="col w25">File</div>
			<div class="col w25">Size</div>
			<div class="col w25">Modified</div>
			<div class="col w25">Price</div>
		</div>
		
		<?php
		// your own software	
		$query = "SELECT * FROM inventory WHERE server_id = 0 AND hacker_id = ".$hackerdata['id'];
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) echo '<div class="row">'.PrintMessage("Info", "You have no files in your inventory.")."</div>";
		echo "<div class='light-bg'>";
		$total_size = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<div class="row hr-light">
				<div class="col w25">'.FileInfo($row['id'], "title").'</div>
				<div class="col w25">'.DisplaySize(FileInfo($row['id'], "size")).'</div>
				<div class="col w25">'.Number2Date($row['datechanged']).'</div>
				<div class="col w25">
					<form method="POST" action="index.php" class="small">
						<input type="hidden" name="h" value="upload">
						<input type="hidden" name="server_id" value="'.$server_id.'">
						<input type="hidden" name="file_id" value="'.$row['id'].'">
						<input type="hidden" name="checksum" value="'.$checksum.'">
						<input type="text" name="price" placeholder="$">
						<input type="submit" value="Upload">
					</form>
				</div>
			</div>';
		}
		echo "</div>";
	}
?>