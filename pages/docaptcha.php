<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php     if ($hackerdata['nextcaptcha_date'] > $now) return "You shouldn't be here yet."; ?>
<?php
	
    // interval/bot check
    AddLog ($hackerdata['id'], "hacker", "interval", "docaptcha", $now);
    
	// Mobile
	if (detect_mobile()) $max_captcha_secs *= 2; // on mobile the max captcha time is risen
	
	// Captcha timeout
	$min_captcha_time = date($date_format, strtotime($_SESSION['captcha_time']." + $min_captcha_secs seconds"));
	$max_captcha_time = date($date_format, strtotime($_SESSION['captcha_time']." + $max_captcha_secs seconds"));
	
	// Captcha timeout
	if($now < $min_captcha_time) return "Session error.";
	if ($now > $max_captcha_time) return "Time out.";
	
    // Captcha hash
    if (!CorrectFormHash("captcha", $_POST[$_SESSION['captcha_field']])) return "Wrong hash!";
    
	// prune to 50 lines per hacker
	$result2 = mysqli_query($link, "SELECT id FROM log WHERE event = 'interval' AND hacker_id = {$hackerdata['id']} ORDER BY date DESC");
	$counter = 0;
	if (mysqli_num_rows($result2) > 50) {
		while ($row2 = mysqli_fetch_assoc($result2) && $counter < 50) $counter++;
		$result2 = mysqli_query($link, "DELETE FROM log WHERE event = 'interval' AND hacker_id = {$hackerdata['id']} AND id < {$row2['id']}");
	}

	// Get the answers
    $answers = $_POST['answer'];
	$counter = 0; 	// Number of captchas solved correctly
	
    for($i = 0; $i < 3; $i++) 
        if($_SESSION['captcha_ans'][$i]['name'] == $answers[$i]) $counter++;
   
	if ($counter == 3) {
		// Solved the captcha correctly
		$minutes = mt_rand($captcha_min_interval, $captcha_max_interval);
		$next_captcha_date = date($date_format, strtotime("+".$minutes." minutes"));
		$result = mysqli_query($link, "UPDATE hacker SET captcha_fails = 0, nextcaptcha_date = '".$next_captcha_date."' WHERE id = ".$hackerdata['id']);
		
		echo '<script type="text/javascript">location.href = "/?'.$_SESSION['redir'].'"</script>';
	}
	else {
		$result = mysqli_query($link, "UPDATE hacker SET captcha_fails = captcha_fails +1 WHERE id = ".$hackerdata['id']);
		return "You failed the captcha, <a href=\"?{$_SESSION['redir']}\">click here to try again</a>.";        
	}  
	$_SESSION['captcha_ans'] = array();	// empty the answers
	$_SESSION['captcha_hits'] = 0;
?>