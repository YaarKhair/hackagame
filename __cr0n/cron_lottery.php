<?php
	include("/var/www/modules/connectdb.php");
	include("/var/www/modules/settings.php");
	include("/var/www/modules/functions.php");
	
	//weekly lottery
	$result = mysqli_query($link, "SELECT * FROM lottery");
	if (mysqli_num_rows($result) < 3) SendMail ("trigger@recipe.ifttt.com", "#lottery", "Because too few lottery tickets were sold this week, there are no weekly winners.<br><br>Be sure to buy tickets for the next lottery!!");
	else {
		// calculate how much the price is
		$tickets = mysqli_num_rows($result);
		$prize = intval(($tickets * $lottery_price) * $lottery_deducted); // % gets cut for costs
		
		$winner_number[1] = 0;
		$winner_number[2] = 0;
		$winner_number[3] = 0;
		$lottery_result = false;
		
		while ($lottery_result == false) {
			$message = "The winners of the HF Lottery of this week are:<br>"; // reset the message
			for ($i = 1; $i <= 3; $i++) {

				mysqli_data_seek($result, 0); // to the start of the recordset
				$random = mt_rand(1, $tickets); //  pick a winner
				// navigate to the random ticket
				for ($counter = 1; $counter <= $random; $counter++) { 
					$row = mysqli_fetch_assoc($result);
				}	
				$winner_id[$i] = $row['hacker_id']; // the winning hacker
				$winner_number[$i] = $row['number']; // the winning ticket
				
				// winner details
				$result2 = mysqli_query($link, "SELECT alias FROM hacker WHERE id = ".$winner_id[$i]);
				$row2 = mysqli_fetch_assoc($result2);
				$winner_name[$i] = $row2['alias'];
				
				// 1st place gets 50%, 2nd gets 35%, 3rd gets 15%
				if ($i == 1) { 
					$share = 0.5;
					$prize_part[$i] = intval($prize * $share);
					$message .= "<br><br>1st: ".$winner_name[1]."<br>Ticket #: ".$winner_number[1]."<br>Prize: ".$currency.number_format($prize_part[1]);
				}	
				if ($i == 2) {
					$share = 0.35;
					$prize_part[$i] = intval($prize * $share);
					$message .= "<br><br>2nd: ".$winner_name[2]."<br>Ticket #: ".$winner_number[2]."<br>Prize: ".$currency.number_format($prize_part[2]);
				}
				if ($i == 3) { 
					$share = 0.15;
					$prize_part[$i] = intval($prize * $share);
					$message .= "<br><br>3rd: ".$winner_name[3]."<br>Ticket #: ".$winner_number[3]."<br>Prize: ".$currency.number_format($prize_part[3]);
				}
			}
			if ($winner_id[1] != $winner_id[2] && $winner_id[1] != $winner_id[3] && $winner_id[2] != $winner_id[3]) $lottery_result = true;
		}	
		// give them the cash
		BankTransfer($winner_id[1], "hacker", $prize_part[1], "HF Lottery 1st Prize");
		BankTransfer($winner_id[2], "hacker", $prize_part[2], "HF Lottery 2nd Prize");
		BankTransfer($winner_id[3], "hacker", $prize_part[3], "HF Lottery 3rd Prize");
		
		// message end
		$message .= "<br><br>We would like to congratulate the winners on their prizes. Be sure to buy tickets for the lottery of next week!!";

		// remove all tickets
		$result = mysqli_query($link, "DELETE FROM lottery");

		// trigger a facebook post via IFTTT.com
		SendMail ("trigger@recipe.ifttt.com", "#update", $message);
	}
	AddLog (0, "hacker", "cron", "cron_lottery", $now);	
?>
