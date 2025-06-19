<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php 
	$message = '<img src="images/banned.png" align="right"><span class="bad"><h2>You are Banned!</h2></span><br>
    <strong>Reason: '. $hackerdata['banned_reason'].'</strong><br>
	<br>
	You can open 1 support ticket to ask for an unban, so if you feel the reason for your ban is not valid please open a support ticket.<br>
	<br>
	[<a href="?h=tickets&show=my">Show Your Support Tickets / Open A New Ticket</a>]<br><br><br><br>';
	//if ($hackerdata['banned_date'] > 0 || $hackerdata['id'] == 1)
		PrintMessage ("Error", $message); 
?>