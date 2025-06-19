<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$tickets =0;
	if (!empty($_POST['tickets'])) $tickets = intval($_POST['tickets']);
	
	// valid number of tickets?
	if ($tickets < 1) return "Incorrect number of tickets. Order cancelled.";	
	// can you buy more?
	$result = mysqli_query($link, "SELECT * FROM lottery WHERE hacker_id = ".$hackerdata['id']);
	$bought = mysqli_num_rows($result);
	if ($bought + $tickets > $lottery_maxtickets) return "The lottery only allows you to buy a maximum of ".$lottery_maxtickets." tickets.";
	
	// not enough tickets left?
	$result = mysqli_query($link, "SELECT * FROM lottery");
	$total_lottery_tickets = mysqli_num_rows($result);
	$tickets_left = $lottery_tickets - $total_lottery_tickets;
	
	if ($tickets > $tickets_left) return "There are only ".$tickets_left." tickets left to sell.";

	// calculate price
	$pay = $tickets * $lottery_price;
	
	// can you afford this?
	$result = mysqli_query($link, "SELECT bankaccount FROM hacker WHERE bankaccount > ".$pay." AND id = ".$hackerdata['id']);
	if (mysqli_num_rows($result) == 0) return "You can not afford this.<br>";

	// pay the tickets
	BankTransfer($hackerdata['id'], "hacker", $pay * -1, $tickets." lottery tickets");

	// insert into lottery database
	for ($i = 0; $i < $tickets; $i++)
		$result = mysqli_query($link, "INSERT INTO lottery (hacker_id, number) VALUES (".$hackerdata['id'].", '".mt_rand(10000,99999)."')");

	PrintMessage ("Success", "You bought ".$tickets." tickets for the upcoming lottery.", "40%");
?>