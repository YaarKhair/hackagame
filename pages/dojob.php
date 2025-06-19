<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($_SESSION['dojob'] != 1) return "Session error";
	$_SESSION['dojob'] = 0;

    // are you allowed yet?
    if ($now <= $hackerdata['nextjob_date']) return "Your system is not ready for another job.";

    if (!CorrectFormHash("job", $_POST[$_SESSION['job_field']])) return "Wrong hash!";

	// reset the session value
	$_SESSION['frm'] = 0;

	$job_id = 0;
	if (!empty($_POST['jobid'])) $job_id = intval($_POST['jobid']);
	
	// let's find the right answer to give on a successfull and failed job
	$query = "SELECT * FROM jobs WHERE id = ".$job_id;
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) 
		return "That job does not exist!";
	
	$row = mysqli_fetch_assoc($result);
	$_SESSION['job_id'] = $job_id; // remember your job, so the next time you open the job list, that job is auto selected.
	
	$interval = ($job_interval * $row['difficulty']) + 20;
	$nextjob_date = date($date_format, strtotime("+".$interval." seconds"));
	$chance = intval(100 - ($row['difficulty'] * 10) + ((EP2Level(GetHackerEP($hackerdata['id'])) / 10) + 1 * 1.5));
	$ep = ($row['difficulty'] * $smallhack_ep_success) + 2;
	
	// Check his last 10 jobs (autoclicker check)
	$bot_score = 5;
	$bot_result = mysqli_query($link, "SELECT date FROM log WHERE event = 'interval' AND details LIKE '%dojob%' AND hacker_id = {$hackerdata['id']} ORDER BY id DESC LIMIT 0,$bot_average_records");
	$intervals = array();
	while($bot_row = mysqli_fetch_assoc($bot_result)) $intervals[] = $bot_row['date'];
	$intervals = array_reverse($intervals);
	$last_job = $intervals[0];
	$average_intervals = 0;
	$seconds_diff_intervals = array();
	for($i = 1; $i < count($intervals); $i++) {
		$interval = SecondsDiff($last_job, $intervals[$i]);	// returns difference in seconds
		$seconds_diff_intervals[] = $interval;
		$last_job = $intervals[$i];
	}
	
	// Find out the average interval
	$average_interval = array_sum($seconds_diff_intervals) / ($bot_average_records - 1);	// -1 because the first record is used as a beginning point and does not contribute to the interval
	
	// Do the threshold
	$after = $average_interval + $bot_average_threshold;
	$before = $average_interval - $bot_average_threshold;
	$job = strtotime($now) - strtotime(array_pop($intervals));
	
	// Check it
	if($job >= $before && $job <= $after) {
		AddLog($hackerdata['id'], 'hacker', 'staff', 'Bot: Detected autoclicker. ['.$bot_score.']', $now);
		$bot_score = mysqli_query($link, "UPDATE hacker SET bot_score = bot_score + $bot_score WHERE id = {$hackerdata['id']}");
	}
	
	// log last 20 smallhacks for interval/bot check
	AddLog ($hackerdata['id'], "hacker", "interval", "dojob ($job_id)", $now);
    
	// Signed up in the last 15 minutes and doing and did a job 1 minute after your captcha? log abuse
	$after = date($date_format, strtotime("{$hackerdata['started']} + $dupe_account_creation minutes"));
	if($after > $now) {
		// Signed up in the last 15 minutes
		// Get his last captcha
		$score = 4;
		$captcha_result = mysqli_query($link, "SELECT date FROM log WHERE event = 'interval' AND details LIKE '%docaptcha%' AND hacker_id = {$hackerdata['id']} ORDER BY id DESC LIMIT 1");
		$captcha_row = mysqli_fetch_assoc($captcha_result);
		$captcha_solve = $captcha_row['date'];
		$future_captcha = date($date_format, strtotime("$captcha_solve + $dupe_captcha_solve minutes"));
		if($future_captcha > $now) {
			AddLog ($hackerdata['id'], "hacker", "staff", "duplicate: Signed up in the last 15 minutes, solved a job after one minute from his first captcha", $now);
			$result = mysqli_query($link, "UPDATE hacker SET duplicate_score = duplicate_score + $score WHERE id = ".$hackerdata['id']);	
		}
	}
	
	// prune to 50 lines per hacker
	$result2 = mysqli_query($link, "SELECT id FROM log WHERE event = 'interval' AND hacker_id = {$hackerdata['id']} ORDER BY date DESC");
	$counter = 0;
	if (mysqli_num_rows($result2) > 50)
		while ($row2 = mysqli_fetch_assoc($result2) && $counter < 50) $counter++; // find the id
	
	if ($counter > 0) $result2 = mysqli_query($link, "DELETE FROM log WHERE event = 'interval' AND hacker_id = {$hackerdata['id']} AND id < {$row2['id']}");

	
	if (WillItWork($chance)) {
		// update job time, ep and bank
		$skill = 0;
		$reward = intval($row['reward'] + ($row['reward'] / 100) * mt_rand(1,100)); // the reward + a % of that reward random added
		if ($hackerdata['network_id'] == 1) AddEP($hackerdata['id'], $ep, $skill, $now, "JOB");
		//if ($hackerdata['network_id'] == 1 || EP2Level($hackerdata['ep']) <= $max_smallhack_ep_level) AddEP($hackerdata['id'], $ep, $skill, $now, "JOB");
		BankTransfer ($hackerdata['id'], "hacker", $reward, "Money received for small hack job");
		PrintMessage ("Success",sprintf($row['success'], $currency.number_format($reward)));
	}
	else { 
		PrintMessage("Error", $row['failed']);
		
		// jailed when failed?
		if (GetJailed($hackerdata['id'], $smallhack_jailchance))
			Jail($hackerdata['id'], $smallhack_jailbail, $smallhack_jailtime, "You got caught doing small computer crimes and were sent to jail.");

	}
    	
	// update job time
	$query = "UPDATE hacker SET nextjob_date = '".$nextjob_date."' WHERE id = ".$hackerdata['id'];
    $result = mysqli_query($link, $query);
	$_SESSION['nextjob_date'] = $nextjob_date;
?>