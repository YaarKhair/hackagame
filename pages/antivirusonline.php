<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
	<img src="images/antivirusonline.png" title="AntivirusOnline" />
	<h1>Welcome to Antivirus Online</h1>
	<div class="row light-bg mv10">
		<div class="col w65">
			When your have a feeling your systems were comprimised, you can use our service to get rid of any infections present on your systems.<br>
			<br>
			<ul>
			<li>We scan your system for infections that are not yet executed</li>
			<li>Our detection rate is 100%</li>
			<li>We offer 1-click Virus scans</li>
			<li>We a cloud based service, you don't need to download anything</li>
			</ul>
			<br>
			<span style="font-style:italic">We clean your system, 100% guaranteed.</span>
		</div>
		<div class='col w35'><img src="images/virus.jpg" align="right" title="AntivirusOnline" class="frame rounded15px fade-out img-right" width="150px"/></div>
	</div>
	<br>
	<div class="row">
		<div class="col w50">
			<div class="accordion">
				<input id="antivirus-pc" type="checkbox" class="accordion-toggle">
				<label for="antivirus-pc">Scan your PC</label>
				<div class="accordion-box">
					<form method="POST" action="index.php">
						<input type="hidden" name="h" value="doantivirusonline">
						<input type="hidden" name="entity" value="hacker">
						<select name='scan_id'>
<?php						
							$result = mysqli_query($link,"SELECT * FROM antivirus WHERE entity = 'hacker'  ORDER BY price ASC");
							while($row = mysqli_fetch_assoc($result)) 
								echo "<option value='{$row['id']}'>{$row['service']} | Level: {$row['level']} | Scantime: {$row['scantime']} | Price: $currency{$row['price']}</option>";						
?>
						</select>
						<input type="submit" value="Start Scan">
					</form>
				</div>
			</div>	
		</div>	
		<div class="col w50">
			<div class="accordion">
				<input id="antivirus-server" type="checkbox" class="accordion-toggle">
				<label for="antivirus-server">Scan your Server</label>
				<div class="accordion-box">
					<form method="POST" action="index.php">
						<input type="hidden" name="h" value="doantivirusonline">
						<input type="hidden" name="entity" value="server">
<?php
						$result = mysqli_query($link, "SELECT id FROM server WHERE hacker_id = {$hackerdata['id']}");
						if (mysqli_num_rows($result) > 0) {
							while($row = mysqli_fetch_assoc($result)) 
								echo "<input type=\"checkbox\" name=\"target_id[]\" value=\"{$row['id']}\" id=\"srv_{$row['id']}\"><label for=\"srv_{$row['id']}\">".GetServerName($row['id'])."</label><br>";
						}
?>
						<strong>Scan Options:</strong><br>
						<select name='scan_id'>
<?php						
							$result = mysqli_query($link,"SELECT * FROM antivirus WHERE entity = 'server'  ORDER BY price ASC");
							while($row = mysqli_fetch_assoc($result)) 
								echo "<option value='{$row['id']}'>{$row['service']} | Level: {$row['level']} | Scantime: {$row['scantime']} | Price: $currency{$row['price']}</option>";						
?>
						</select><br>
						<input type="checkbox" name="refresh_ip" id="refresh_ip"><label for="refresh_ip">Refresh IP ($10,000 per server)</label><br>
						<input type="submit" value="Start Scan">
					</form>
				</div>
			</div>	
		</div>	
	</div>		