<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	
	// Get the parameters
	$email = '';
	if(!empty($_POST['email'])) $email = sql($_POST['email']);
	
	$message = '';
	if(!empty($_POST['message'])) $message = htmlspecialchars(nl2br($_POST['message']));	// no sql sanitization because it won't be stored anywhere
	
	// Validate the email
	if (!isvalidemail($email)) return "This email is not valid.";
	
	// Check if the email already exists
	$exists = mysqli_get_value("id", "hacker", "email", $email, false);
	if($exists > 0) return "This email is already registered";
	
	// Everything checks out, build the body of the message
	$referral_code = SHA1($hackerdata['started']);
	$title = "You were invited by {$hackerdata['alias']}";

	$msg = "Hi $email! <br><br>";
	$msg .= "You were invited to play the game HackerForever by {$hackerdata['alias']}. HackerForever is an MMORPG revolving around the world of hackers!<br>";
	$msg .= "He wrote this message for you:<br><br>";
	$msg .= $message." <br><br>";
	$msg .= "If you would like to accept his invitation you can regsiter <a href='$gameurl/guest.php?referral_code=$referral_code'>here</a><br><br>";
	$msg .= "Hope to see you in the game!<br>Game Administration.";

	SendMail($email, $title, $msg);
	PrintMessage("Success", "Your invitation was sent to $email");
?>