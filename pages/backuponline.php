<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$_SESSION['loaded'] = 0; // anti refresh
	$system_size = 0;
	$fee = 0;
	// system size * fee = costs of the backup
	$result = mysqli_query($link, "select sum(product.size) as syssize FROM system LEFT JOIN product ON system.product_id = product.id WHERE (product.in_shop = 1) AND system.hacker_id = ".$hackerdata['id']);
	if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc ($result);
		$system_size = $row['syssize'];
		$fee = round($system_size * $backup_fee);
	}	

	// total size of all backups
	$total_size = 0;
	$result = mysqli_query($link, "select sum(product.size) as totalsize FROM system LEFT JOIN product ON system.product_id = product.id WHERE system.backup_id <> 0");
	if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc ($result);
		$total_size = $row['totalsize'];
	}	
?>

	<img src="images/backuponlinelogo.png" title="BackupOnline" />
	<img src="images/backuponline.jpg" align="right" title="BackupOnline" class="frame rounded fade-out img-right"/>
	<h1>Welcome to BackupOnline</h1>
	<span style="font-style:italic">Your data is safe with us!</span>
	<ul>
	<li>We are a professional data storage company with highly secured data centers</li>
	<li>We backup your system state (your installed software) but not your downloads</li>
	<li>Backups can be restored once</li>
	<li>We offer 1-click Backup and 1-click Restore</li>
	<li>We are currently storing <?php echo DisplaySize($total_size); ?> data</li>
	</ul>
	<br>
	<form method="POST" action="index.php">
		<input type="hidden" name="h" value="dobackuponline">
		<input type="hidden" name="action" value="backup">
		<input type="submit" value="Backup Now! (<?php echo DisplaySize($system_size)." ".$currency.number_format($fee);?>)">
	</form><br><br>
<?php
	$result = mysqli_query($link, "select sum(product.size) as backupsize FROM system LEFT JOIN product ON system.product_id = product.id WHERE (product.in_shop = 1) AND system.backup_id = ".$hackerdata['id']);
	if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc ($result);
		if (!is_null($row['backupsize'])) {
			$backup_size = $row['backupsize'];
?>			
			<form method="POST" action="index.php">
				<input type="hidden" name="h" value="dobackuponline">
				<input type="hidden" name="action" value="restore">
				<input type="submit" value="Restore <?php echo Number2Date($hackerdata['backup_date']); ?> (<?php echo DisplaySize($backup_size)." ".$currency."0"; ?>)">
			</form>
<?php
		}
	}
?>
