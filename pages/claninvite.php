<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$error = "";
	
	if ($hackerdata['clan_id'] == 0) $error = "You are not in a clan.";
	if ($hackerdata['clan_council'] == 0) $error = "You are not part of the clan council.";
	
	if ($error == "") {
		// show invite form
		$result = mysqli_query($link, "SELECT * FROM `clan` WHERE `id` = ".$hackerdata['clan_id']);
		$row = mysqli_fetch_assoc($result);
		echo '
                <h2>Invite a hacker</h2>
				<form method="POST" action="index.php" name="hf_form" class="light-bg">
					<input type="hidden" name="h" value="doinvite">
					<div class="row"><div class="col w50">Hacker:</div><div class="col w50"><input type="text" name="username" value="" size="10"></div></div>
					<div class="row"><div class="col w50">Message:</div><div class="col w50"><input type="text" name="reason" value="" size="40"></div></div>
					<div class="row"><div class="col w100"><strong>[NOTE: An invite stays valid for '.$invite_keep.' days]</strong><br><input type="submit" value="Invite"></div></div>
				</form>
			<br><br>
				<h2>Current Open Invites</h2>
				<div class="row th light-bg">
					<div class="col w25">Invited</div>
					<div class="col w25">Date</div>
					<div class="col w25">Invited By</div>
					<div class="col w25">Action</div>
				</div>';
				
		//  currently active invites
		$query = "SELECT invite.id, invite.date, invite.inviter_id, hacker.alias FROM invite INNER JOIN hacker ON invite.hacker_id = hacker.id WHERE invite.clan_id = ".$hackerdata['clan_id']." ORDER BY date DESC";
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) {
			echo '<div class="row"><div class="col w100">No active invites</div></div>';
		}
		else {
			$color = 0;
			echo '<div class="dark-bg">';
			while($row = mysqli_fetch_assoc($result)) {
				$inviter = mysqli_get_value("alias", "hacker", "id", $row['inviter_id']);
				echo '
					<div class="row hr-light"><div class="col w25">'.$row['alias'].'</div>
						<div class="col w25">'.Number2Date($row['date']).'</div>
						<div class="col w25">'.$inviter.'</div>
					<div class="col w25"><a href="index.php?h=invite&action=delete&id='.$row['id'].'">Revoke</a></div></div>';
			}
			echo '</div>';
		}
		echo '<script type="text/javascript">document.hf_form.username.focus();</script>';	
	}
	else PrintMessage ("Error", $error, "40%");
?>