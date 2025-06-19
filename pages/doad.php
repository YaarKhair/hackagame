<?php
    $year = '00';
    if (!empty($_POST['year'])) $year = intval($_POST['year']);
	if ($year != substr($now, 0, 4)) return "You can only place ads in this year.";

    $month = '00';
    if (!empty($_POST['month'])) $month = intval($_POST['month']);
    if (strlen($month) == 1) $month = "0".$month;
	if ($month < 1 || $month > 12) return "Invalid month.";

    $day = '00';
    if (!empty($_POST['day'])) $day = intval($_POST['day']);
    if (strlen($day) == 1) $day = "0".$day;
	if ($day < 1 || $day > 31) return "Invalid day.";

    $hour = '00';
    if (!empty($_POST['hour'])) $hour = intval($_POST['hour']);
    if (strlen($hour) == 1) $hour = "0".$hour;
	if ($hour < 0 || $hour > 23) return "Invalid hour.";
    
    $message = "";
    if (!empty($_POST['message'])) $message = sql($_POST['message']);
    
    if ($message == "") return "The message can not be empty";
    
    $ad_date = $year.$month.$day.$hour;
    $check_date = substr(date($date_format, strtotime("+1 hour")),0,10);
    $oneweek = substr(date($date_format, strtotime("+7 days")),0,10);

	// is everything cool?
	include ("modules/permissions.php");
	if (!$is_staff) {
		if ($ad_date > $oneweek) return "You can only place an ad up to 7 days in the future.";
    	if ($ad_date < $check_date) return "You need to choose a date in the future.";
    	if ($hackerdata['ads'] == 0) return "You have reached your daily limit of $daily_ads ads.";
	}

    // block free?
    $result = mysqli_query($link, "SELECT id FROM ad WHERE date = '$ad_date'");
    if (mysqli_num_rows($result) > 0) return "This ad block is already sold.";
    
    // how are you going to pay for this?
	if (!empty($_POST['pay'])) $pay = $_POST['pay'];
	else return "You need to select how you are going to pay for this ad.";
	
	// handle the payment
	if ($pay == "money") {		
		if ($hackerdata['bankaccount'] < $ad_price) return "You don't have enough money.";
	    BankTransfer ($hackerdata['id'], "hacker", $ad_price * -1, "Frontpage Ad");
	}
	else {
		if ($hackerdata['hackpoints_credit'] < $ad_hp) return "You don't have enough hackpoints.";	
		AddHackpoint ($hackerdata['id'], 0, $ad_hp  * -1, "Frontpage Ad");
	}
	
    // insert the ad
    $result = mysqli_query($link, "INSERT INTO ad (hacker_id, date, message) VALUES ({$hackerdata['id']}, '$ad_date', '$message')");
    $result = mysqli_query($link, "UPDATE hacker SET ads = ads -1 WHERE id = {$hackerdata['id']}");
    PrintMessage ("Success", "Ad block succesfully bought. It will show on $year-$month-$day at $hour:00");
    
    include ("pages/ad.php");
?>    