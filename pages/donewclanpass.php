<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?
	if (!IsFounder($hackerdata['id'])) {
		$error = "You are not the clan founder!";
		AddLog ($hackerdata['id'], "hacker", "abuse", "Tried to reset a clan password while not being the founder.", $now);
	}

	if ($error == "") {
		$newpass = createrandomPassword();

		// update the password for this hacker
		$result = mysqli_query($link, "UPDATE `clan` SET `bankaccount_password` = '".sha1($newpass)."' WHERE `id` = ".$hackerdata['clan_id']);
		
		// send an email
		$mail_body = "Hi, ".$hackerdata['alias'].". You requested a new clan password. The new clan password is: ".$newpass; //mail body
		$subject = "New password"; //subject
		$header = "From: HF Game Account <$gameemail>\r\n"; //optional headerfields
		ini_set('sendmail_from', $gameemail); // just to be sure
		mail($hackerdata['email'], $subject, $mail_body, $header); //mail command :)

		PrintMessage ("Success", "A new password has been sent to ".$hackerdata['email'], "40%");
	}
	else PrintMessage ("Error", $error, "40%");
?>