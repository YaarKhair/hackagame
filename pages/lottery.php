<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($hackerdata['network_id'] != 2)
		return "You are connected to ".mysqli_get_value("name", "network", "id", 1).". The function you are trying to use is unavailable from this network.";
	
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
?>
<img src="./images/cheapcash.png" title="CheapCash Lottery"><br><br>
<h2>Buy Tickets (<?php echo $currency.number_format($lottery_price); ?>/ticket)</h2>
<br>
<p>Current jackpot: <?php echo $currency.number_format($total_lottery_cash); ?>.</p>
<p>Tickets sold: <?php echo number_format($tickets_sold); ?>.</p>
<p>Tickets left: <?php echo number_format($tickets_left); ?>.</p>
<br>
<p>The prize winners will be announced every Friday at 16:00 (local HF time).</p>
<br>
<form method="POST" action="index.php">
	<input type="hidden" name="h" value="dolottery">
	<input type="text" name="tickets" size="2"> tickets <input type="submit" value="Buy">
</form>

<br><br>

<div class="accordion">
	<input id="lottery" type="checkbox" class="accordion-toggle" checked>
	<label for="lottery">You have <?php echo number_format($hacker_tickets); ?> tickets for the upcoming lottery</label>
	<div class="accordion-box">
		<?php
		if ($hacker_tickets > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				echo $row['number'].'<br>';
			}
		} else {
			echo "No tickets, buy some now!";
		}
		?>
	</div>
</div>