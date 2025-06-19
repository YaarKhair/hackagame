<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    if(isset($_POST['password1']) && isset($_POST['password2']) && isset($_POST['token'])) {
		// init
		$password1 = '';
		$password2 = '';
		$token = '';
		// read values
		if(!empty($_POST['password1'])) $password1 = sql($_POST['password1']);
		if(!empty($_POST['password2'])) $password2 = sql($_POST['password2']);
		if(!empty($_POST['token'])) $token = sql($_POST['token']);
		// checks
		if ($token == '') return "Invalid token.";
		if(!isValidUserPassword($password1)) return "Password needs to contain at least one uppercase, one lowercase and one symbol.";
		
		// Checking stuffs
		if($password1 == $password2) {
			$result = mysqli_query($link, "SELECT email, salt FROM hacker WHERE pass_resettoken = '$token'");
			if(mysqli_num_rows($result) == 0) return "Invalid token.";
			$row = mysqli_fetch_assoc($result);
			
			$salt = generatePassword($salt_length);
			$newPass = SHA1($password_key.$password1.$salt);
			// So the token checks out and so do the passwords, let's update his password and update his token so he can't use it again
			$result = mysqli_query($link, "UPDATE hacker SET password = '$newPass', pass_resettoken = '0', salt = '$salt' WHERE pass_resettoken = '$token'");
			
			// Sending email to the fucker telling him that his password got reset
			$email = $row['email'];
			$title = "Password reset successful";
			$body = "Your password was reset successfully from IP: ".GetUserIP()."<br><br>";
			$body .= "If you did not do this reset, then please report to Game Administration.";
			SendMail($email, $title, $body);
			PrintMessage("Success","Your password was successfully reset. You may login now.");
		}
		else return "Passwords did not match.";
	} 
    else return "Not all fields were sent.";
?>