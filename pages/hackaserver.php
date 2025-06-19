<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
	<script>
		function check() {
			var input = confirm("Are you sure you want to overclock your PC? \n Overclocking your PC will increase 10% success chance but will also disable you from performing PC hacks for an hour and server hacks for two hours");
			if(input == false) {
				document.getElementById("overclock").checked = false;
			}
		}
 	</script>
<?php
	// are you allowed to hack, or do you have to wait?
	if ($hackerdata['network_id'] != 2) return "You are not connected to the internet.";

	$_SESSION['doserverhack'] = 1;

?>
	<div class="row">
		<div class="col w50">
			<h1>Hack a server</h1>
			<form action="index.php" method="POST" name="hf_form">
				<input type="hidden" name="h" value="doserverhack">
				<?php AddFormHash("srv1"); ?>
				<div class="row light-bg">
					<input type="text" class="icon address w100i" placeholder="IP address" name="server_ip"><br>
					<?php 
						$result = mysqli_query($link, "SELECT count(product.id) as NumItem, product.id, product.title FROM inventory INNER JOIN product ON inventory.product_id = product.id WHERE inventory.hacker_id = ".$hackerdata['id']. " AND `code` = 'SERVERHACK' AND `server_id` = 0 GROUP BY product.id");
						if (mysqli_num_rows($result) == 0) PrintMessage ("error", 'Your HDD does not contain tools to attack a server!');
						else {
							while ($row = mysqli_fetch_assoc($result)) echo '<INPUT TYPE="radio" NAME="product_id" ID="'.$row['id'].'" VALUE="'.$row['id'].'" checked><label for="'.$row['id'].'">'.$row['title'].' ('.$row['NumItem'].')</label><br>';
						}
					?>
					<?php if($now >= $hackerdata['next_overclock']) echo '<input type="checkbox" name="overclock" id="overclock" onclick="check();"><label for="overclock">Overclock</label>'; ?>
				</div>
				<input type="submit" value="Initiate Hack!">
			</form>
		</div>
		<!--<div class="col w10">
		&nbsp;
		</div>-->
		<div class="col w50">
			<h1>Reset a server</h1>
			<form action="index.php" method="POST" name="hf_form2">
				<input type="hidden" name="h" value="doserverreset">
				<?php AddFormHash("srv2"); ?>
				<div class="row light-bg">
					<input type="text" class="icon address w100i" placeholder="IP address" name="server_ip"><br>
					<input type="text" class="icon serverpass w100i" placeholder="Old Password" name="old_password"><br>
					<input type="text" class="icon serverpass w100i" placeholder="New Password" name="new_password"><br>
				</div>
				<input type="submit" value="Reset Password!">
			</form>
		</div>
	</div>
<?php
	// hack a server
/*	echo '<div class="row">
			<div class="col w50">
			<h1>Hack a server</h1>
				<form method="POST" action="index.php" name="hf_form">
					<input type="hidden" name="h" value="doserverhack">
				<div class="row hr-light">
					<div class="col w35">
						<input type="text" class="icon address" placeholder="IP address" name="server_ip"><br>';
						if($now >= $hackerdata['next_overclock']) echo '<input type="checkbox" name="overclock" id="overclock" onclick="check();"><label for="overclock">Overclock</label>';

					echo'</div>
					<div class="col w65">';
					// first check if you have an offence tool
					$result = mysqli_query($link, "SELECT count(product.id) as NumItem, product.id, product.title FROM inventory INNER JOIN product ON inventory.product_id = product.id WHERE inventory.hacker_id = ".$hackerdata['id']. " AND `code` = 'SERVERHACK' AND `server_id` = 0 GROUP BY product.id");
					if (mysqli_num_rows($result) == 0) PrintMessage ("error", 'Your HDD does not contain tools to attack a server!');
					else {
						while ($row = mysqli_fetch_assoc($result)) echo '<INPUT TYPE="radio" NAME="product_id" ID="'.$row['id'].'" VALUE="'.$row['id'].'" checked><label for="'.$row['id'].'">'.$row['title'].' ('.$row['NumItem'].')</label><br>';
					}

				echo '</div></div></div>';
				
    AddFormHash ("srv1");
    
	echo '		<div class="row">
						<input type="submit" value="Initiate Hack">
				</div>
				</form>
		<br><br>';

	// reset a server
	echo '		<div class="col w50">
				<h1>Reset a server</h1>
				<form method="POST" action="index.php" name="hf_form2">
					<input type="hidden" name="h" value="doserverreset">
				<div class="row hr-light">
                	<input type="text" class="icon address" placeholder="IP address" name="server_ip"><br>
					<input type="text" class="icon serverpass" placeholder="Old Password" name="old_password"><br>
					<input type="text" class="icon serverpass" placeholder="New Password" name="new_password">
				</div>
				<div class="row">
					<input type="submit" value="Reset Password">
				</div></div>';
                
   AddFormHash ("srv2");

	echo '</form>
		<script type="text/javascript">document.hf_form.server_ip.focus();</script>';*/
?>
