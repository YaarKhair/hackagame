<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if ($hackerdata['support_tickets'] < 1) PrintMessage ("Error", "You have 0 remaining Support slots.", "40%");
	else {
		$warning = 'Before opening a new ticket, please do the following:<br>
					<ul>
					<li>Read the <a href="?h=wiki">Wiki</a></li>
					<li>Ask others in <a href="?h=chat">chat</a> for help</li>
					<li>Make sure <a href="?h=tickets&show=all">the ticket doesn\'t already exist</a></li>
					</ul>
					Only when you did all those things you can open a new ticket.';
					
		PrintMessage ("warning", $warning);
		
		// when you're banned you can only open tickets of the type "mods only". lets set that here
		$banned_sql = "";
		if ($hackerdata['banned_date'] > 0) $banned_sql = " AND banned = 1";
		
		echo '
				<h2>New Ticket</h2>
					<div class="light-bg">
					<form method="POST" action="index.php">
						<input type="hidden" name="h" value="doticket">
						<input type="hidden" name="action" value="post_new">
						<div class="row"><div class="col w20">Title</div><div class="col w80"><input type="text" name="title" maxlength="30"></div></div>
						<div class="row"><div class="col w20">Type</div><div class="col w80"><select name="type_id">';
							$result2 = mysqli_query($link, "SELECT id, type_desc FROM ticket_type WHERE active = 1 AND level <= ".EP2Level(GetHackerEP($hackerdata['id'])).$banned_sql); // level dependant ticket types.
							while ($row2 = mysqli_fetch_assoc($result2)) echo '<option value="'.$row2['id'].'">'.$row2['type_desc'];
						echo '</select></div></div>
						<div class="row"><div class="col w20">Message</div><div class="col w80"><div class="note">Be as specific as you can be. Unclear tickets will be closed.</div><textarea name="message" class="w100i h350"></textarea></div></div>
						<div class="row"><input type="submit" value="Submit New Ticket"></div>
					</form></div>';
					
	}		
?>		
