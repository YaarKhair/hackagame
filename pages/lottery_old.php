<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($hackerdata['network_id'] != 2)
		return "You are connected to ".mysqli_get_value("name", "network", "id", 1).". The function you are trying to use is unavailable from this network.";

	echo '<img src="./images/cheapcash.png" title="CheapCash Lottery" / ><br><br>';
	
	// total lottery pot
	$result = mysqli_query($link, "SELECT id FROM lottery");
	$tickets_sold = mysqli_num_rows($result);
	$total_lottery_cash = $tickets_sold * $lottery_price;
	if ($total_lottery_cash == NULL) { $total_lottery_cash = 0; }
	
	// tickets left to sell
	$tickets_left = $lottery_tickets - $tickets_sold;
	
	// tickets of this hacker
	$result = mysqli_query($link, "SELECT id, number FROM lottery WHERE hacker_id = ".$hackerdata['id']);
	$hacker_tickets = mysqli_num_rows($result);
	
	echo '
		<table width="60%">
			<caption>Buy Tickets ('.$currency.number_format($lottery_price).' per ticket)</caption>
			<tbody>
				<tr><td>
					Current jackpot: '.$currency.number_format($total_lottery_cash).'.<br>
					Tickets sold: '.number_format($tickets_sold).'.<br>
					Tickets left: '.number_format($tickets_left).'.<br>
					You have '.number_format($hacker_tickets).' tickets for the upcoming lottery.<br>
					The prize winners will be announced every Friday at 16:00 (local HF time).<br/ ><br>
					<form method="POST" action="index.php">
					<input type="hidden" name="h" value="dolottery">
					<input type="text" name="tickets" size="2"> tickets <input type="submit" value="Buy">
				</td></tr>	
			</tbody>
		</table>
		<br><br>';
	
	echo '
		<table width="100%">
			<caption>Current Tickets</caption>
			<tbody>
				<tr><td>';
			if ($hacker_tickets > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					echo $row['number'].', ';
				}
			}
			else echo "No tickets.";
	echo '</td>		
			</tr>	
			</tbody>
		</table>
		<br><br>';
?>