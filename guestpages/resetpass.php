<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>

<?php
    if ($hackerdata['id'] > 0 && $hackerdata['id'] != 1) return "You can not access this page when you are logged in.";
    
    if(isset($_POST['email'])) {
        // step 1
		$email = '';
		if(!empty($_POST['email'])) $email = sql($_POST['email']);
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE email = '$email'");
		if(mysqli_num_rows($result) == 0) return "Email address not found.";
		
		// Preparing the email
		$token = sha1(uniqid());
		$result = mysqli_query($link, "UPDATE hacker SET pass_resettoken = '$token', resettoken_date = '$now' WHERE email = '$email'");
		$title = "Password reset";
		$body = "You have requested a password reset from IP: ".GetUserIP()."<br><br>";
		$body .= "Click on the following link to reset your password:<br>";
		$body .= "<a href=\"$gameurl/guest.php?h=resetpass&token=$token\">http://www.hackerforever.com/guest.php?h=resetpass&token=$token</a><br><br>";
		$body .= "If you did not ask for a password reset, please report this incident to Game Administration.";
		SendMail($email, $title, $body);
		PrintMessage("Success", "An email to reset your password has been sent.");
	} 
    elseif(isset($_GET['token'])) {
        // step 2
    	$token = '';
		if(!empty($_GET['token'])) $token = sql($_GET['token']);
		if($token == '') return "Invalid token (1).";
		$result = mysqli_query($link, "SELECT id FROM hacker WHERE pass_resettoken = '$token'");
		if(mysqli_num_rows($result) == 0) return "Invalid token (2).";
		
		// Everything checks out let's show them a form where they can reset their password
?>		
		<section id="login">
			<div class="container">
				<h1>Reset your password</h1>
				<div id="login-wrap">
					<form action="guest.php" method="post">
					  <input type="hidden" name="token" value="<?php echo $token; ?>">
						<input type="hidden" name="h" value="doresetpass">
						<input type="password" placeholder="Password" class="icon pass" name="password1">
						<input type="password" placeholder="Password" class="icon pass" name="password2">
						<input type="submit" value="Reset password">
					</form>
				</div> 
			</div>
		</section>		
<?php		
	} 
    else {
        // step 0
?>
		<section id="login">
			<div class="container">
				<h1>Reset your password</h1>
				<div id="login-wrap">
					<form action="guest.php" method="post">
						<input type="hidden" name="h" value="resetpass">
						<input type="text" placeholder="Email" class="icon email" name="email">
						<input type="submit" value="Request New Password">
					</form>
				</div> 
			</div>
		</section>		
<?php
	}
?>