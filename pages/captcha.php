<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php if ($hackerdata['nextcaptcha_date'] > $now) return "Reload error."; ?>
<?php
	// Increase captcha hits
	if(isset($_SESSION['captcha_hits']) && $_SESSION['captcha_hits'] > 0) $_SESSION['captcha_hits']++;
	else $_SESSION['captcha_hits'] = 1;
	
	// Check his captcha hits
	if($_SESSION['captcha_hits'] > 5) {
		// If you've hit the captcha 5 times without solving it, you are on a 5 minute lockout
		$lockout_time = date($date_format, strtotime("+ 5 minutes"));
		$result = mysqli_query($link, "UPDATE hacker SET captcha_lockout_till = '$lockout_time' WHERE id = ".$hackerdata['id']);
		AddLog($hackerdata['id'], "hacker", "staff", "Locked out of captcha for 5 minutes for refreshing excessively.", $now);
	}
	
	// Lockout?
	if($hackerdata['captcha_lockout_till'] > $now && $hackerdata['id'] != 8157) return "You are on a 5 minute captcha lockout for refreshing the captcha excessively.";
	
    // interval/bot check
    AddLog ($hackerdata['id'], "hacker", "interval", "captcha", $now);
	
    // too many false tries
    if ($hackerdata['captcha_fails'] > 4) {
        $lockout_till = date($date_format, strtotime("+".$faultylogin_interval." minutes"));
        $result2 = mysqli_query($link, "UPDATE hacker SET lockout_till = '$lockout_till', lockout_reason = 'Too many failed captcha attempts.', captcha_fails = 0 WHERE id = ".$hackerdata['id']);
    	include ("pages/logout.php");
    	return "Too many failed captcha tries";
    }	
    
    // prune to 50 lines per hacker
    $result2 = mysqli_query($link, "SELECT id FROM log WHERE event = 'interval' AND hacker_id = {$hackerdata['id']} ORDER BY date DESC");
    $counter = 0;
    if (mysqli_num_rows($result2) > 50) {
    	while ($row2 = mysqli_fetch_assoc($result2) && $counter < 50) $counter++;
    	$result2 = mysqli_query($link, "DELETE FROM log WHERE event = 'interval' AND hacker_id = {$hackerdata['id']} AND id < {$row2['id']}");
    }	

    // init
    $num_images = 5;                // how many images per object?
    $num_indentify = 3;             // how many object do they need to identify?
    $num_answers = 5;               // number of answers to chose from
    $width = 100;
    $height = 100;
	$_SESSION['captcha_ans'] = array(); // Array of correct answers
	$filters = array(20 => IMG_FILTER_NEGATE, 10 => IMG_FILTER_GRAYSCALE, 5 => IMG_FILTER_EMBOSS, 10 => IMG_FILTER_MEAN_REMOVAL)

    // start of form
?>
    <h1>Are you human?</h1>
	<?php PrintMessage("info", "Identify the three objects below by selecting what they best represent."); ?>
    <form method="POST" action="index.php">
        <input type="hidden" name="h" value="docaptcha">
        
<?php        
	$answers = array();
	$objects = array();
	$limit = $num_indentify * $num_answers;
	
	// Select the images to display
	$result = mysqli_query($link, "SELECT name, image_code FROM (SELECT captcha_images.image_code AS image_code, captcha_objects.name FROM captcha_objects LEFT JOIN captcha_images ON captcha_images.object_id = captcha_objects.id ORDER BY RAND()) as z GROUP BY z.name ORDER BY rand() LIMIT $limit") or die(mysqli_error($link)); // this query is important as fuck, it ensures that we select random images and unique objects
	while($row = mysqli_fetch_assoc($result)) $answers[] = array("code" => $row['image_code'], "name" => $row['name']);
	for($i = 0; $i < $num_indentify; $i++)
		$objects[$i] = array_slice($answers, $num_answers * $i, $num_answers);
	
	// Loop through to display the pictures and options
    for ($i = 0; $i < $num_indentify; $i ++) {
        $answers = array();
        		
		// Pick the first one as the correct answer
		$_SESSION['captcha_ans'][] = $objects[$i][0];
		
		// Add filters
		$image = ImageCreateFromString(base64_decode($objects[$i][0]['code']));
		
		// Add filters to the image before displaying it (so people can't create signatures for the pictures)
		$r = rand(0, 255);
		$g = rand(0, 255);
		$b = rand(0, 255);
		$a = rand(30, 127);
		imagefilter($image, IMG_FILTER_COLORIZE, $r, $g, $b, $a);
		foreach($filters as $chance => $filter)
			If(WillItWork($chance)) imagefilter($image, $filter);

        // Generate the output of the image, encode it and include it as a data URI
		ob_start();
		imagejpeg($image);
		$base64 = base64_encode(ob_get_contents());
		ob_end_clean();
		
		// Shuffle 'em
		shuffle($objects[$i]);
		
        echo '<div style="float: left; padding: 10px"><img src="data:image/jpeg;base64,'.$base64.'" width="'.$width.'" height="'.$height.'"><br><select name="answer[]"><option value=""></option>';

        // show answers
        foreach ($objects[$i] as $answer)
            echo '<option value="'.$answer['name'].'">'.$answer['name'].'</option>';
        
        // close options
        echo '</select></div>';
    }
    
    AddFormHash ("captcha");
	
	// Set the current time of loading in a session to check later if they submitted it in time
	$_SESSION['captcha_time'] = $now;
	
    // end of form
?>
        <br clear=all>
        <input type="submit" value="Submit">
    </form>