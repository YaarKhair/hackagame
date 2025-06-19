<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
	<img src="images/antivirusonline.png" title="AntivirusOnline" />
	<img src="images/virus.jpg" align="right" title="AntivirusOnline" class="frame rounded15px fade-out img-right"/>
	<h1>Welcome to AntivirusOnline</h1>
	<span style="font-style:italic">We clean your system, 100% guaranteed.</span>
	<ul>
	<li>We scan your system for infections that are not yet executed</li>
	<li>Our detection rate is 100%</li>
	<li>We offer 1-click Virus scans</li>
	</ul>
	<br clear="all">
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
						<select name="server_id">
<?php
						$result = mysqli_query($link, "SELECT id FROM server WHERE hacker_id = {$hackerdata['id']}");
						if (mysqli_num_rows($result) > 0) {
							while($row = mysqli_fetch_assoc($result)) 
								echo "<option value='{$row['id']}'>".GetServerName($row['id'])."</option>";
						}
?>
						</select>
						<select name='scan_id'>
<?php						
							$result = mysqli_query($link,"SELECT * FROM antivirus WHERE entity = 'server'  ORDER BY price ASC");
							while($row = mysqli_fetch_assoc($result)) 
								echo "<option value='{$row['id']}'>{$row['service']} | Level: {$row['level']} | Scantime: {$row['scantime']} | Price: $currency{$row['price']}</option>";						
?>
						</select>
						<input type="submit" value="Start Scan">
					</form>
				</div>
			</div>	
		</div>	
	</div>		
<?php						
		/*	
			$result = mysqli_query($link, "SELECT id FROM server WHERE hacker_id = {$hackerdata['id']}");
			if (mysqli_num_rows($result) == 0)
				$message .= 'You have no servers.';
			else {
				$message .= '<select name="server">';
				while($row = mysqli_fetch_assoc($result)) 
					$message .= "<option value='{$row['id']}'>".GetServerName($row['id'])."</option>";
				$message .= '</select>';
			}	
			$message .= "</td></tr>";
			$message .= "<tr><td>Service: </td><td><select name='service'>";
	
			$result = mysqli_query($link,"SELECT * FROM antivirus_online ORDER BY level ASC");
			while($row = mysqli_fetch_assoc($result)) 
				$message .= "<option value='{$row['id']}'>{$row['service']} | Level: {$row['level']} | Scantime: {$row['scantime']} | Price: $currency{$row['price']}</option>";
				
			$message .= "</td></tr>";
			$message .= "</table>";
			$message .= '<input type="submit" value="Start scan"></form>';
	PrintMessage ("Antivirus Online Service", $message, "100%");*/
?>