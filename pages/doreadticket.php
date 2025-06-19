<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$error = "";
	$ticket_id = 0;
	if (!empty($_GET['ticket_id'])) $ticket_id = intval($_GET['ticket_id']);
	
	$result = mysqli_query($link, "SELECT ticket.*, ticket_type.type_desc, ticket_type.id as type_id, ticket_status.status, ticket_status.status_desc FROM ticket LEFT join ticket_type ON ticket.type_id = ticket_type.id LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE ticket.respons_id = 0 AND ticket.id = $ticket_id");
	if (mysqli_num_rows($result) == 0) $error = "Ticket not found.";
	else {
		$row = mysqli_fetch_assoc($result);
		// MOD ONLY tickets
		if ($row['hacker_id'] != $hackerdata['id'] && $row['type_id'] == 4 &&(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2))) $error = "This ticket can only be viewed by the ticket owner and staff.";
	}

	if ($error == "") {
		echo '<div class="accordion">
              	<input id="ticket" type="checkbox" class="accordion-toggle" checked>
                <label for="ticket">'.$row['title'].'</label>
                <div class="accordion-box">
					<div class="row hr-light">
						<div class="col w20">Date opened</div>
						<div class="col w80">'.Number2Date($row['date']).'</div>
					</div>
					<div class="row hr-light">
						<div class="col w20">Opened by</div>
						<div class="col w80">'.ShowHackerAlias($row['hacker_id'], 0, false).'</div>
					</div>
					<div class="row hr-light">
						<div class="col w20">Type</div>
						<div class="col w80">'.$row['type_desc'].'</div>
					</div>
					<div class="row hr-light">
						<div class="col w20">Status</div>
						<div class="col w80">'.$row['status_desc'].'</div>
					</div>
					<div class="row hr-light">
						<div class="col w20">Message</div>
						<div class="col w80">'.ReplaceBBC($row['message']).'</div>
					</div>';

					// bug or request? show poll
					if ($row['type_id'] == 1 || $row['type_id'] == 3) {
						if ($row['type_id'] == 1) {
							$answer1 = "This applies to me too";
							$answer2 = "This does not apply to me";
						}
						if ($row['type_id'] == 3) {
							$answer1 = "I like this";
							$answer2 = "I do not like this";
						}
							
						echo '<div class="row hr-light"><div class="col w20">Votes</div><div class="col w80">';
						$poll = '';
						// display if you have voted already
						$result2 = mysqli_query($link, "SELECT a_id FROM poll_vote WHERE ticket_id = $ticket_id AND hacker_id = {$hackerdata['id']}");
						if (mysqli_num_rows($result2) == 0) {
							$vote = "You have not voted yet.";
							$vote_text = "Please vote";
						}
						else {
							$row2 = mysqli_fetch_assoc ($result2);
							if ($row2['a_id'] == 1) $vote = $answer1;
							else $vote = $answer2;
							$vote_text = "Change vote";
						}
						echo "<strong>Your vote: $vote</strong><br>";
						
						if ($row['status'] != 3) {
							$poll .= '<form method="POST" action="index.php">
										<h3>'.$vote_text.'</h3>
										<input type="hidden" name="h" value="doticket">
										<input type="hidden" name="action" value="vote">
										<input type="hidden" name="ticket_id" value="'.$ticket_id.'">
										<INPUT TYPE="radio" NAME="poll_answer" VALUE="1" id="ans1"><label for="ans1">'.$answer1.'</label><br>
										<INPUT TYPE="radio" NAME="poll_answer" VALUE="2" id="ans2"><label for="ans2">'.$answer2.'</label><br>
										<input type="submit" value="Vote!">
										</form><br>';
						}		
						
						// Show results
						$poll .= "<h3>Poll Results</h3>";
						$result2 = mysqli_query($link, "SELECT id FROM poll_vote WHERE ticket_id = $ticket_id");
						$total_votes = mysqli_num_rows($result2);
						
						$result2 = mysqli_query($link, "SELECT a_id, count(a_id) as total_votes FROM poll_vote WHERE ticket_id = $ticket_id GROUP BY a_id ORDER BY total_votes DESC");
						while ($row2 = mysqli_fetch_assoc($result2)) {
							if ($row2['a_id'] == 1) $answer = $answer1;
							else $answer = $answer2;
							$poll .= ShowProgress($row2['total_votes'], $total_votes)."&nbsp;".$answer."&nbsp;(".$row2['total_votes'].")<br>";
						}
						$poll .= "<br>Total votes: $total_votes";
						
						echo $poll.'</div></div>';
					}
					echo '</div></div>';			
		// all replies
		echo '<div class="accordion">
              	<input id="replies" type="checkbox" class="accordion-toggle">
                <label for="replies">Replies</label>
                <div class="accordion-box">';
				
		$result2 = mysqli_query($link, "SELECT id, respons_id, hacker_id, date, message FROM ticket WHERE respons_id = $ticket_id ORDER BY date DESC");
		if (mysqli_num_rows($result2) == 0) echo "<div id='row'>".PrintMessage("info", "There are no replies to this ticket yet.").'</div>';
		else {
			while ($row2 = mysqli_fetch_assoc($result2)) {
				if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) $delete = '[<a href="?h=doticket&action=delete_reply&ticket_id='.$row2['respons_id'].'&reply_id='.$row2['id'].'">Delete Reply</a>]';
				else $delete = '';
				echo '<div class="row hr-light mv10">';
				if(!empty($delete)) echo "<div class='row'><div class='note'>".$delete.'</div></div>';
				echo "<div class='row'>".ShowHackerAlias($row2['hacker_id'], 0, false).' replied on '.Number2Date($row2['date']).'</div>';
				echo "<div class='row'>".replaceBBC($row2['message']).'</div>';
				echo "</div>";
			}		
		}		
				echo "</div>";
			echo "</div>";
				
		// you are not staff, so are you allowed to reply?
		if (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2) && !InGroup($hackerdata['id'], 6)) {
			if ($row['status'] == 3) // closed
				return "You can not reply to this ticket because this ticket is closed.";
			
			if ($row['status'] == 1 && $row['hacker_id'] != $hackerdata['id']) // new
				return "You can not reply to this ticket, because the staff has to review the ticket first.";
		}
		
?>
	<div class="accordion">
    	<input id="add_reply" type="checkbox" class="accordion-toggle">
        <label for="add_reply">Add A Reply</label>
        <div class="accordion-box">
			<div class="row">
				<div class="col w70">
					<form method="POST" action="" name="hf_form">
						<input type="hidden" name="h" value="doticket">
						<input type="hidden" name="action" value="post_reply">
						<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
						<textarea name="message" class="w100i" placeholder="Reply..."></textarea><br>
						<?php if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) { ?>
						<input type="checkbox" name="im" id="im1" checked><label for="im1">Notify by IM</label><br>
						<input type="checkbox" name="email" id="email1"><label for="email1">Notify by EMAIL</label><br>
						<?php } ?>
						<input type="submit" value="Post Reply">
					</form>
				</div>
				<div class="col w30">
					<?php PrintMessage ("Warning", "Only add replies to tickets if you have something to contribute to the ticket's discussion. Comments like 'I agree' or something similar are unwanted and will be deleted."); ?>
				</div>
			</div>
		</div>
	</div>
	<?php if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2) || InGroup($hackerdata['id'], 6)) { ?>
	<div class="accordion">
    	<input id="mod_options" type="checkbox" class="accordion-toggle">
        <label for="mod_options">Moderator Options</label>
        <div class="accordion-box">
			<div class="row hr-light">
				<form method="POST" action="" name="hf_form">
					<input type="hidden" name="h" value="doticket">
					<input type="hidden" name="action" value="change_title">
					<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
					<input type="text" name="title" maxlength="50" placeholder="New title..">
					<input type="checkbox" name="im" id="im2" checked><label for="im2">Notify by IM</label>
					<input type="checkbox" name="email" id="email2"><label for="email2">Notify by EMAIL</label>
					<input type="submit" value="Change Title">
				</form>
			</div>
			<div class="row hr-light">
				<form method="POST" action="" name="hf_form">
					<input type="hidden" name="h" value="doticket">
					<input type="hidden" name="action" value="change_status">
					<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
					<select name="status_id">
					<?php
						$result2 = mysqli_query($link, "SELECT id, status, status_desc FROM ticket_status ORDER BY status");
						while ($row2 = mysqli_fetch_assoc($result2)) {
							if ($row2['status'] == 1) $tickstat = "New";
							if ($row2['status'] == 2) $tickstat = "Open";
							if ($row2['status'] == 3) $tickstat = "Closed";
							echo '<option value="'.$row2['id'].'">['.$tickstat.'] '.$row2['status_desc'].'</option>';
						}	
					?>
					</select>
					<input type="checkbox" name="im" id="im3" checked><label for="im3">Notify by IM</label>
					<input type="checkbox" name="email" id="email3"><label for="email3">Notify by EMAIL</label>
					<input type="submit" value="Change Status">
				</form>
			</div>
			<div class="row hr-light">
				<form method="POST" action="index.php" name="hf_form">
					<input type="hidden" name="h" value="doticket">
					<input type="hidden" name="action" value="change_type">
					<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
					<select name="type_id">	
					<?php
					$result2 = mysqli_query($link, "SELECT id, type_desc FROM ticket_type");
					while ($row2 = mysqli_fetch_assoc($result2)) echo '<option value="'.$row2['id'].'">'.$row2['type_desc'].'</option>';
					?>
					</select>
					<input type="checkbox" name="im" id="im4" checked><label for="im4">Notify by IM</label>
					<input type="checkbox" name="email" id="email4"><label for="email4">Notify by EMAIL</label>
					<input type="submit" value="Change Type">
				</form>
			</div>
			<div class="row hr-light">
				<form method="POST" action="" name="hf_form">
					<input type="hidden" name="h" value="doticket">
					<input type="hidden" name="action" value="reset_vote">
					<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
					<input type="submit" value="Reset votes">
				</form>
			</div>
<?php
		}
	}
	else PrintMessage ("Error", $error);
?>