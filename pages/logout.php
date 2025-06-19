<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	AddLog ($hackerdata['id'], "hacker", "interval", "logout", $now); // bot detection
	if (isset($_SESSION)) {
		// Unset all of the session variables. 
		$_SESSION = array(); 

		// Finally, destroy the session. 
		session_destroy(); 	
	}
 	echo '<script type="text/javascript">self.location="'.$gameurl.'";</script>';
?>
