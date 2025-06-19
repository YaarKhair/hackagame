<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['doaccessfbi'] != 1) return "Session error";
	$_SESSION['doaccessfbi'] = 0;

	$password = "";
	if (!empty($_POST['pass'])) $password = sql($_POST['pass']);
	
	// is this the correct password?
	if ($password != $hackerdata['fbi_serverpass']) return "Password incorrect. Make sure caps-lock is off as the password is case sensitive. Please try again...";
	// are you on the internet?
	if ($hackerdata['network_id'] != 2) $error = "You are not connected to the internet.";
	// is your gateway online?
	if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) $error = "Your clans' gateway is offline!";
	// do you own a server?
	if (NumServers($hackerdata['id']) == 0) $error = "You need to own at least one server to initiate the attack from";
	
	$_SESSION['dofbidatabase'] = 1;
	
	echo '
		<script language="JavaScript" type="text/javascript"> 
		<!--
		function PrintInForm(alias) {
			hf_form.alias.value=alias;
		}	
		//-->
		</script>
	';
	
	// show the FBI most wanted list
	echo '<div class="row mv10 hr-light">
			<img src="images/fbicybercrimedb.png" title="FBI" />
			<h2>FBI Most Wanted List</h2>
			<div class="row th">
				<div class="col w33">DB_RECORD_ID</div>
				<div class="col w33">ALIAS</div>
				<div class="col w33">DATE ADDED</div>
			</div>
		';	
		
	$result = mysqli_query($link, "SELECT id, alias, fbi_wanteddate FROM hacker WHERE fbi_wanteddate > 0 AND (npc = 0 OR npc = {$hackerdata['id']}) ORDER BY ep DESC");
	if (mysqli_num_rows($result) == 0) 
		echo '<div class="row">List is empty...</div>';
	else {
		$conter = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<div class="row"><div class="col w33">'.$row['id'].'</div>';
			echo '<div class="col w33"><span onClick="PrintInForm(\''.$row['alias'].'\');">'.$row['alias'].'</span></div>';
			echo '<div class="col w33">'.Number2Date($row['fbi_wanteddate']).'</div></div>';
		}
	}
	echo '</div>';
	
	// add or remove record
	echo '<div class="row">
			<div class="col w33">
				<h2>Add a hacker</h2>
				<form method="POST" action="index.php" name="add_form">
					<input type="hidden" name="h" value="dofbidatabase">
					<input type="hidden" name="option" value="1">
					known hacker alias: <input type="text" name="alias"><br>
					last known ip: <input type="text" name="ip"><br>
					<input type="submit" value="Execute">
				</form>	
			</div>
			<div class="col w33">
				<h2>Remove a hacker</h2>
				<form method="POST" action="index.php" name="add_form">
					<input type="hidden" name="h" value="dofbidatabase">
					<input type="hidden" name="option" value="2">
					known hacker alias: <input type="text" name="alias"><br>
					<input type="submit" value="Execute">
				</form>
			</div>
			<div class="col w33">
				<h2>View log</h2>
				<form method="POST" action="index.php" name="add_form">
					<input type="hidden" name="h" value="dofbidatabase">
					<input type="hidden" name="option" value="3">
					<input type="submit" value="Execute">
				</form>
			</div>
		</div>	
	';	
?>
