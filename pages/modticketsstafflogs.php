<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load", $now); die(); } ?>
<?php 
	$ticket_result = mysqli_query($link, "SELECT ticket.id, ticket.title, ticket.date FROM ticket WHERE hacker_id = {$row['id']} AND respons_id = 0 ORDER BY DATE DESC LIMIT 0, 50");
	$notes_result = mysqli_query($link, "SELECT * FROM modnote WHERE hacker_id = {$row['id']} ORDER BY date DESC");
?>
<!---- Tickets ----->
<div class="row">
		<div class="accordion">
			<input id="modtickets" type="checkbox" class="accordion-toggle">
			<label for="modtickets">Tickets</label>
			<div class="accordion-box">
				<div class="row mv5">
					<?php
					while($tickets = mysqli_fetch_assoc($ticket_result))
						echo '['.$tickets['id'].']&nbsp;<a href="?h=doreadticket&ticket_id='.$tickets['id'].'">'.$tickets['title'].'</a> on '.Number2Date($tickets['date']).'<br>';
					?>
				</div>
			</div>
		</div>
</div>
<div class="row">
		<div class="accordion">
			<input id="modnotes" type="checkbox" class="accordion-toggle">
			<label for="modnotes">Notes</label>
			<div class="accordion-box">
				<?php
				while($notes = mysqli_fetch_assoc($notes_result)) {
					$delete = '<div class="note">[<a href="?h=domodnote&action=delete_note&note_id='.$notes['id'].'&hacker_id='.$row['id'].'">Delete Note</a>]</div>';
					echo '<div class="row hr-light mv10">'.$delete.ShowHackerAlias($notes['creator_id'], 0, false).' replied on '.Number2Date($notes['date']).'<br><br>'.replaceBBC($notes['message']).'</div>';
				}
				?>
				<form method="POST" action="index.php">
					<input type="hidden" name="h" value="domodnote">
					<input type="hidden" name="action" value="post_note">
					<input type="hidden" name="hacker_id" value="<?php echo $row['id']; ?>">
					<textarea class="w100i" name="message"></textarea><br>
					<input type="submit" value="Add Note">
				</form>	
			</div>
		</div>
	</div>
</div>