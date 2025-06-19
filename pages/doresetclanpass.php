<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$error = "";
	// are you a clans' founder?
	$result = mysqli_query($link, "SELECT id FROM clan WHERE founder_id = ".$hackerdata['id']." AND id = ".$hackerdata['clan_id']);
	if (mysqli_num_rows($result) == 0) $error = "Fubar.";
	
	$row = mysqli_fetch_assoc($result);
	
	if ($error == "") {
		$newpass = generatePassword($random_password_length);
		$pass = sha1($newpass);

		// update the password for this clan
		$result = mysqli_query($link, "UPDATE clan SET bankaccount_password = '$pass' WHERE id = ".$hackerdata['clan_id']);
		
		// send an IM
		SendIM (0, $hackerdata['id'], "New clan password", "Hi, ".$hackerdata['alias'].". You requested a new clan password. The new clan password is: ".$newpass, $now);
		PrintMessage ("Success", "A new password has been sent via IM", "40%");
	}
	else PrintMessage ("Error", $error, "40%");
?>