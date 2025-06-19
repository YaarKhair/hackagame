<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$_SESSION['doserversoftware'] = 1;
	
	// servers
	$result = mysqli_query($link, "SELECT server.*, product.code, product.title as product_title, product.image FROM server LEFT JOIN product ON server.product_id = product.id WHERE hacker_id = ".$hackerdata['id']." ORDER BY id ASC");
	
	if (mysqli_num_rows($result) == 0)
		return "No servers to manage.";

	// economy
	$result2 = mysqli_query($link, "SELECT spam_economy, phishing_economy, porn_economy, filesharing_economy FROM economy ORDER BY id DESC LIMIT 1");
	$row2 = mysqli_fetch_assoc($result2);
	$spam_rev = $spamserver_revenue * ($row2['spam_economy']/100);
	$phishing_rev = $phishingserver_revenue * ($row2['phishing_economy']/100);
	$porn_rev = $pornserver_revenue * ($row2['porn_economy']/100);
	$filesharing_rev = $filesharingserver_revenue * ($row2['filesharing_economy']/100);
	
	// Installables
	$installable_not_gw = array("SERVERFTP" => "FTP", "SERVERSPAM" => "Spam", "SERVERPORN" => "Porn", "SERVERPHISHING" => "Phishing", "SERVERSHARING" => "Filesharing");
	$installable_gw = array("GATEWAY_SMALL" => "Small_GW", "GATEWAY_MEDIUM" => "Medium_GW", "GATEWAY_LARGE" => "Large_GW");
	$installable_always = array("SERVERCAREPACK" => "Care Pack", "SERVERSCANNER" => "Server Scanner", "SERVERFIREWALL" => "Firewall", "SERVERHONEY" => "Honey Pot");
	echo '
			<h1>Server Manager</h1>
			<div class="row th hr-light">
				<div class="col w20">Info</div>
				<div class="col w20">Health</div>
				<div class="col w20">Software</div>
				<div class="col w20">Profit</div>
				<div class="col w20">Settings</div>
			</div>
			';
	while ($row = mysqli_fetch_assoc($result)) {
		// found out what kind of software is running: $,@ or X
		$software = "";
		$ftp_form = "";
		$cloak_form = "";
							
		// is this an ftp server?
		if ($row['code'] == "SERVERFTP") { 
			$ftp_form = "[<a href=\"?h=ftp&server_ip={$row['ip']}\">Connect</a>]<br>";
			$ftp_form .= "</a><input type=\"text\" name=\"ftp_title\" placeholder=\"FTP Name\" size=\"10\" maxlength=\"25\" value=\"{$row['ftp_title']}\"><br>";
			$ftp_form .= "<input type=\"password\" name=\"ftp_password\" placeholder=\"Password\" size=\"10\" maxlength=\"25\" value=\"{$row['ftp_password']}\"><br>";
			$ftp_form .= "<input type=\"text\" name=\"ftp_motd\" size=\"10\" maxlength=\"100\" placeholder=\"MOTD\" value=\"{$row['ftp_motd']}\"><br>";
			$checked = '';
			if ($row['ftp_public'] == 1) $checked = ' checked';
			$ftp_form .= "<input type=\"checkbox\" name=\"ftp_public\" $checked id=\"ftp_{$row['id']}\"><label for=\"ftp_{$row['id']}\">Public?</label>";
		} 

		// is this a cloaked server?
		if ($row['code'] == "SERVERCLOAKER") { 
			$cloak_form = '<input type="text" name="cloak_color" size="6" maxlength="6" value="'.$row['cloak_color'].'">&nbsp;[color]<br>';
		} 
					
		// what can we install on this server?
		if ($row['gateway'] == 0) $install = array_merge($installable_not_gw, $installable_always);
		else $install = array_merge($installable_gw, $installable_always);

		// hourly profit
		$rev = 0;
		if ($row['product_id'] == 9) $rev = $spam_rev;
		if ($row['product_id'] == 14) $rev = $phishing_rev;
		if ($row['product_id'] == 15) $rev = $porn_rev;
		if ($row['product_id'] == 28) $rev = $filesharing_rev;
		$rev *= ($row['efficiency']/100); // server efficiency
		$rev = round($rev);
		
		echo '	<div class="row hr-light dark-bg">
					<div class="col w20">'.GetServername($row['id']).'<br>
					['.$row['ip'].']<br>
					[<a href="?h=readlog&log=server&server_id='.$row['id'].'">server.log</a>]</div>
					<div class="col w20">'.ShowProgress($row['efficiency'], 100, "Health").'<br>
					'.ShowProgress($row['firewall'], 100, "Firewall").'
					</div>
					<div class="col w20">
						<form action="index.php" method="POST" class="alt-design">
							<input type="hidden" name="h" value="doserversoftware">
							<input type="hidden" name="server_id" value="'.$row['id'].'">
							<select name="product_code">';
							echo '<option value=""></option>';
							foreach($install as $code => $title) {
								$selected = '';
								if($row['code'] == $code) $selected = "selected";
								echo "<option value='$code' $selected>$title</option>";
							}
							echo '</select>
							<input type="submit" value="Install">
						</form>		
					</div>
					<div class="col w20">
						<form>
							<input type="hidden" name="h" value="doserver">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="hidden" name="action" value="reset_profit">
                        	<p>Hourly revenue: '.$currency.$rev.'</p>
                        	<p>Total profit: '.$currency.number_format($row['profit']).'</p>
                        	<p><input type="submit" class="bg-red" value="Reset"></p>
                        </form>
					</div>
					<div class="col w20">
						<form method="POST" action="index.php">'. 
							$ftp_form.$cloak_form.'
							<input type="hidden" name="h" value="doserver">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="hidden" name="action" value="update">
                            <input type="text" class="icon serverpass" placeholder="Password" name="password">
                            <input type="submit" value="Save">
						</form>
						<form method="POST" action="index.php">
							<input type="hidden" name="h" value="doserver">
							<input type="hidden" name="id" value="'.$row['id'].'">
							<input type="hidden" name="action" value="sell">
                            <input type="submit" class="bg-red" value="Sell" disabled>
						</form>	
					</div> 
				</div>';	
	}
?>