<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$error = "";
	$email = "";
	if (!empty($_POST['email'])) $email = sql($_POST['email']);

	// lets find out if this account is already activated
	$result = mysqli_query($link, "SELECT id, alias, activationcode, banned_date FROM hacker WHERE email = '".$email."'");
	if (mysqli_num_rows($result) == 0) $error = "No account found with this email address.";
	else {
		$row = mysqli_fetch_assoc($result);
		if ($row['activationcode'] != '') $error = "You need to activate this account first!";
		if ($row['banned_date'] > 0) $error = "This account is banned.";
	}
	
	if ($error == "") {
		$newpass = createrandomPassword();

		// update the password for this hacker
		$result = mysqli_query($link, "UPDATE hacker SET password = '".sha1($newpass)."' WHERE id = ".$row['id']);
		
		// send an email
		$mail_body = "Hi, ".$row['alias'].". A new password has been requested for your account from IP address ".GetUserIP().". If you did not request this password then please report it to the game administration. Your new password is: ".$newpass; //mail body
		$subject = "New password"; //subject
		SendMail($email, $subject, $mail_body); //mail command :)
		AddLog (0, "hacker", "password", "password reset request for {$row['alias']} from ip ".GetUserIP(), $now); 
		PrintMessage ("Success", "A new password has been sent to ".$email, "40%");
	}
	else PrintMessage ("Error", $error, "40%");
?>