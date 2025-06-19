<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// read economy details
	$result = mysqli_query($link, "SELECT spam_economy, phishing_economy, porn_economy, filesharing_economy FROM economy ORDER BY id DESC LIMIT 1");
	$row = mysqli_fetch_assoc($result);
	$spam_rev = $spamserver_revenue * ($row['spam_economy']/100);
	$phishing_rev = $phishingserver_revenue * ($row['phishing_economy']/100);
	$porn_rev = $pornserver_revenue * ($row['porn_economy']/100);
	$filesharing_rev = $filesharingserver_revenue * ($row['filesharing_economy']/100);
?>	
<h1>HF Economy</h1>
<img src="images/economy_spam.png" class="frame rounded bg" title="Spam Economy" /><br><i>Spam Economy</i> | Current Hourly Revenue: <?php echo $currency.number_format($spam_rev); ?><br><br>
<img src="images/economy_phishing.png" class="frame rounded bg" title="Phishing Economy" /><br><i>Phishing Economy</i> | Current Hourly Revenue: <?php echo $currency.number_format($phishing_rev); ?><br><br>
<img src="images/economy_porn.png" class="frame rounded bg "title="Porn Economy" /><br><i>Porn Economy</i> | Current Hourly Revenue: <?php echo $currency.number_format($porn_rev); ?><br><br>
<img src="images/economy_filesharing.png" class="frame rounded bg" title="File Sharing Economy" /><br><i>File Sharing Economy</i> | Current Hourly Revenue: <?php echo $currency.number_format($filesharing_rev); ?><br><br>
<?php PrintMessage ("info", "Y-axis : percentage of how well the software performs due to economic influences<br>X-axis : day of the month<br>Hourly revenue is based on server with 100% health.", "60%");
