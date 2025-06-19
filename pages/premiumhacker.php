<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
if (!empty($_GET['status'])) {
	$status = sql($_GET['status']);
	if ($status == "ok") PrintMessage ("Info", "Thank you for your donation. Your reward is coming ASAP!", "40%");
	else return "Donation cancelled.";
} else {
?>

<h1>Donate - show you care!</h1>

<p>Renting a server (VPS), paying for the domain, it all costs money. This game is my hobby, so I don't get paid for maintaining it. It costs me money. This game can only exist through your donations. Donating shows you care and rewards you with "premium" status (<a href="<?php echo $gameurl; ?>/?h=wiki&title=Premium+Hacker">what is this?</a>).</p>
<br>
<?php PrintMessage ("info", "After making a donation, <strong>please send an ingame IM to [[@chaozz]]</strong>."); ?>

<h2>You can donate in various ways. There is always a way for you to show your support!</h2>
<br>

<div class="row">
	<div class="col w75">
		
		<div class="accordion">
			<input id="donate_tshirt" type="checkbox" class="accordion-toggle" checked>
			<label for="donate_tshirt">Method 1. Buy a T-shirt! + <?php echo $premium_time; ?> months premium</label>
			<div class="accordion-box">
				<div id="merch" class="center">
					<p>Be the coolest kid in school or the most respected guy at work. On top of the cool shirt, you also get <?php echo $premium_time; ?> months premium on your account.</p>
					<br>
					<img src="theme/images/hfshirt.png" width="75%" class="rounded15px" alt="Official Merchandise">
					<h3 class="mv10">Visit the store closest to your home!</h3>
					<a href="http://hackerforever.spreadshirt.com" target="_blank"><img src="theme/images/flag_us.png" alt></a><a href="http://hackerforever.spreadshirt.nl" target="_blank"><img src="theme/images/flag_eu.png" alt></a>
				</div>
			</div>
		</div>

		<div class="accordion">
			<input id="donate_paypal" type="checkbox" class="accordion-toggle" checked>
			<label for="donate_paypal">Method 2. Donate using PayPal + <?php echo $premium_time; ?> months premium for each €5 donated</label>
			<div class="accordion-box">
				<p>The easiest way to donate is via Paypal. For each €5 you donate you get <?php echo $premium_time; ?> months worth of premium on your account.</p>
				<br>
				<?php PrintMessage ("info", "<strong>Minimum donation amount: €5</strong><br>Also, please be SURE to include your hacker alias in the text field so we know which account to award with the Premium Hacker tag."); ?>
				
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="alt-design center">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="SF9KF63VEL4R6">
					<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
					<img alt="" border="0" src="https://www.paypalobjects.com/nl_NL/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
		</div>

		<div class="accordion">
			<input id="donate_bank" type="checkbox" class="accordion-toggle" checked>
			<label for="donate_bank">Method 3. Donate using Bank Transfer</label>
			<div class="accordion-box">
				<p>Please contact <?php echo replaceBBC('[[@chaozz]]'); ?> for bank account details.</p>
			</div>
		</div>
		
	</div>
	<div class="col w25">
		

		<div class="accordion">
			<input id="donors_list" type="checkbox" class="accordion-toggle" checked>
			<label for="donors_list">Premium Members</label>
			<div class="accordion-box">
				<?php
					// list of premium gamers
					$list = "";
					$result = mysqli_query($link, "SELECT id FROM hacker WHERE donator_till > '$now' AND donator = 1 ORDER BY donator_till DESC");
					if (mysqli_num_rows($result) > 0) {
						while ($row = mysqli_fetch_assoc($result)) $list .= ShowHackerAlias($row['id'])."<br>";
						echo $list;
					}
				?>
			</div>
		</div>
		
	</div>
</div>

<?php		
	}
	/*
	
		<br><br>
		<table><thead><tr><th>Method 3: Donate using SMS</th></tr></head>
		<tbody><tr><td>
			<h4>Least favorable due to bad rates. Minimum donation amount: €5,50</h4>
			<br>
			<script language="javascript">widgetKey('f311ce07cd115a0a00270b0f6b925461d5161f3b','', '', '', '#79c837');</script>
			<br>
		</td></tr></tbody></table>	
	
				You can wire money to my bank account:
			<pre>
  Bank name      : Postbank
  Account number : 3265609
  Owner          : E. Wenners
  City           : Medemblik
  IBAN           : NL60INGB0003265609  
  BIC            : INGBNL2A 
  Description    : HF donation [username]  (please fill in your username
                                            for the ingame reward)
			</pre>
*/
?>
