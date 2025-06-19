<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<script type="text/javascript">
document.onkeyup = CalcInterest;
function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}
function CalcInterest()
{
	var amount = document.hf_form.amount.value;
	amountclan = Math.round ( (amount / 100) * (100 - <?php echo $clan_interest; ?>) );
	amounthacker = Math.round ( (amount / 100) * (100 - <?php echo $hacker_interest; ?>) );
	document.getElementById('clansum').innerHTML = '<span>' + addCommas(amountclan) + '</span>'; 
	document.getElementById('hackersum').innerHTML = '<span>' + addCommas(amounthacker) + '</span>'; 
}
</script>
<?php
	$account = "";
	$pass = "";
	$requirelogin = false;
	
	// bank or hacker?
	if (!empty($_REQUEST['account'])) $account = sql($_REQUEST['account']);
	
	// 1 time login per session or when the password changes
	if ($account == "hacker") 
	{
		$pass2check = $hackerdata['password'];
		if ($_SESSION['banklogin'] != $pass2check) $requirelogin = true;
	}	
	else 
	{ 
		$pass2check = mysqli_get_value ("bankaccount_password", "clan", "id", $hackerdata['clan_id']);
		if ($_SESSION['clanbanklogin'] != $pass2check) $requirelogin = true;
	}
		
	if ($requirelogin) 
	{
		// Hackers use a salt
		if($account == "hacker") if (!empty($_POST['pass'])) $pass = sha1($password_key.$_POST['pass'].$hackerdata['salt']);
		
		// Clans dont use a salt
		if($account == "clan") if(!empty($_POST['pass'])) $pass = sha1($_POST['pass']);
		
		if ($pass == $pass2check) 
		{
			if ($account == "hacker") $_SESSION['banklogin'] = $pass;
			else $_SESSION['clanbanklogin'] = $pass;
		}	
		else 
		{
			echo '
				<div align="center">
					<br>
					<br>
					<img src="images/cyberbanklogo.png" title="Cyber Bank" / >
					<br>
					<br>
					<form name="hf_form" method="POST" action="index.php">
						<input type="hidden" name="h" value="dobankaccount">
						<input type="hidden" name="account" value="'.$account.'">
						<input type="password" name="pass" size="25" maxlength="40"><br>
						<input type="submit" value="Secure login">
					</form>
				</div><br><br>';
			echo '<script type="text/javascript">document.hf_form.pass.focus();</script>';
			return ("Login required.");
		}
	}
		
	if ($account != "clan" && $account != "hacker") return "Invalid account";
	
	if ($account == "clan" && !IsOnlineServer(GetGateway($hackerdata['clan_id']))) return "Your gateway is currently offline.";

	if ($account == "hacker") $amount = mysqli_get_value("bankaccount", $account, "id", $hackerdata['id']);
	if ($account == "clan") $amount = mysqli_get_value("bankaccount", $account, "id", $hackerdata['clan_id']);
	
	$_SESSION['dosendmoney'] = 1;
	echo '
			<h1>Welcome to your online Cyber Bank Account</h1>
			<div class="light-bg">
			<div class="row"><div class="col w50">Current Balance:</div><div class="col w50">'.$currency.number_format($amount).'</div></div>
			<form method="POST" action="index.php" name="hf_form">
				<input type="hidden" name="h" value="dosendmoney">
				<input type="hidden" name="from" value="'.$account.'">
				<div class="row"><div class="col w50">Amount:</div><div class="col w50"><input type="text" name="amount" value="" size="10"></div></div>
				<div class="row"><div class="col w50">To:</div><div class="col w50"><INPUT TYPE="radio" NAME="to" VALUE="hacker" id="hacker" checked><label for="hacker">Hacker (receives: <span id="hackersum">0</span>)</label><br>
								<INPUT TYPE="radio" NAME="to" VALUE="clan" id="clan"><label for="clan">Clan (receives: <span id="clansum">0</span>)</label></div></div>
				<div class="row"><div class="col w50">Alias:</div><div class="col w50"><input type="text" name="name" value="" size="20"></div></div>
				<div class="row"><div class="col w50">Reason:</div><div class="col w50"><input type="text" name="reason" value="" size="40" maxlength="50"></div></div>
				<div class="row"><div class="col w100"><br><input type="submit" value="Send cash"></div></div>
			</form>
			</div><br>
			<h2>Last 50 transactions</h2>
				<div class="row th light-bg">
					<div class="col w20">Date</div>
					<div class="col w20">Amount</div>
					<div class="col w60">Details</div>
				</div>
				<div class="dark-bg">';	
		
	//  show bank log last xx transactions
	if ($account == "hacker") $query = "SELECT id, details, date FROM log WHERE hacker_id = ".$hackerdata['id']." AND event = 'bank' AND date <= '".$now."' ORDER BY date DESC, id DESC LIMIT 50";
	else $query = "SELECT id, details, date FROM log WHERE clan_id = ".$hackerdata['clan_id']." AND event = 'bank' AND date <= '".$now."'ORDER BY id DESC LIMIT 50";
	$result = mysqli_query($link, $query);
	
	if (mysqli_num_rows($result) == 0) {
		echo '<div class="row"><div class="col w100">No transactions found</div></div>';
	}
	else {
		while($row = mysqli_fetch_assoc($result)) {
			list($amount, $reason) = explode('|', $row['details']); // details = amount|reason
			
			echo '
				<div class="row hr-light">
				<div class="col w20">'.Number2Date($row['date']).'</div>
				<div class="col w20">'.number_format($amount).'</div>
				<div class="col w60">'.$reason.'</div>
				</div>';
				$lowest_id = $row['id'];
		}
	}
	echo "</div>";
	echo '<script type="text/javascript">document.hf_form.amount.focus();</script>';	
?>