<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($hackerdata['network_id'] != 2) 
		return "You are connected to ".mysqli_get_value("name", "network", "id", 1).". The function you are trying to use is unavailable from this network.";
		
	$_SESSION['doaccessfbi'] = 1; // anti refresh
	echo '
	<div align="center"><img src="images/fbi_large.png" title="FBI" /><br>
	Welcome to the FBI Central Database Server. Please login...<br><br>
		<form method="post" action="index.php" name="hf_form">
			<input type="hidden" name="h" value="doaccessfbi">
			<input type="password" size="20" maxlength="50" name="pass" class="icon serverpass"><br>
			<input type="submit" value="Secure Login">
		</form>	
	<script type="text/javascript">document.hf_form.pass.focus();</script>	
	</div>';
?>