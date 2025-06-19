<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	//	if (!HasInstalled($hackerdata['id'], "EMAIL")) return "You do not have an email client installed";
	if (!empty($_GET['folder'])) $folder = sql($_GET['folder']);
	
	// if $folder is specified, we load the outbox
	if ($folder == "outbox") {
		$query = "SELECT im.*, hacker.id as hacker_id, hacker.clan_id as hackerclan_id FROM im LEFT JOIN hacker ON im.reciever_id = hacker.id WHERE im.sender_id={$hackerdata['id']} AND sender_del = 0 ORDER BY im.date DESC";
		$label = "To";
	}
	// if $folder is not set, or set to anything other then outbox, we load the inbox
	else {	
		$query = "SELECT im.*, hacker.id as hacker_id, hacker.clan_id as hackerclan_id FROM im LEFT JOIN hacker ON im.sender_id = hacker.id WHERE im.reciever_id = {$hackerdata['id']} AND reciever_del = 0 AND date < '$now' ORDER BY im.pinned DESC, im.date DESC";
		$label = "From";
		$folder = "inbox";
	}	
	$result = mysqli_query($link, $query);
	
	// max messages
	if ($hackerdata['donator_till'] > $now) $max = $premium_max_messages;
	else $max = $max_messages;
	
?>
		<h1><?php echo ucwords($folder); ?> (last <?php echo $max; ?> messages, messages older than <?php echo $log_keep; ?> days will be deleted)</h1>
		<div class="row th dark-bg">
			<div class="col w20">Date</div>
			<div class="col w35"><?php echo $label; ?></div>
			<div class="col w45">Title</div>
		</div>
			<form method="POST" action="index.php" name="hfform" class="small alt-design">
				<input type="hidden" name="h" value="doim">
<?php
		if (mysqli_num_rows($result) == 0) echo '<div class="row">'.PrintMessage("info", "You have no messages.").'</div>';
		else {
			echo '<div class="dark2-bg">';
			$counter = 0; // count the messages
			$prev_pinned = 0; // was the previous message pinned?
			
			while ($row = mysqli_fetch_assoc($result)) {
				$counter ++;
				if ($counter <= $max) {
					$pre = ''; $post = '';
					if ($row['unread'] == 1) { 
						$pre = '<span class="bold white">';
						$post = '</span>';
					}
					if ($row['title'] == NULL) $title = "[NO SUBJECT]";
					else $title = $row['title'];
					
					if ($folder == "inbox") {
						if ($row['pinned'] == 1 && $folder == "inbox") { $title .= '&nbsp;<img src="images/pinned.gif" title="Pinned" />'; $prev_pinned = 1; }
						if ($row['pinned'] == 0 && $prev_pinned == 1) echo '<div class="row hr-dark"></div>';
						$prev_pinned = $row['pinned'];
					}
										
					// a clan message should be visible by an extra icon
					if ($row['clan_id'] > 0) $icon = '<img src="images/council.png" title="clan message" class="badge">';
					else $icon = '';
					
					echo '
						<div class="row mv10 hr">
							<div class="col w20"><input type="checkbox" name="message_id[]" value="'.$row['id'].'" id="msg_'.$row['id'].'"><label for="msg_'.$row['id'].'">'.Number2Date($row['date']).'</label></div>
							<div class="col w35">'.ShowHackerAlias($row['hacker_id'], $row['hackerclan_id']).'</div>
							<div class="col w45">'.$icon.'<a href="?h=doreadim&id='.$row['id'].'">'.$pre.$title.$post.'</a></div>
						</div>';
						$last_date = $row['date'];
				}
				else {
					if  ($folder == "inbox")
						$result2 = mysqli_query($link, "UPDATE im SET reciever_del = 1, unread = 0, pinned = 0 WHERE id = {$row['id']}");
					else	
						$result2 = mysqli_query($link, "UPDATE im SET sender_del = 1 WHERE id = {$row['id']}");
				}		
			}
			echo "</div><br>";
		}
?>
		<div class="accordion">
			<input id="mailaction" type="checkbox" class="accordion-toggle" checked>
			<label for="mailaction">Actions</label>
			<div class="accordion-box">
				<div class="row">
					<input type="button" name="CheckAll" value="Check All" onclick="checkAll(document.hfform['message_id[]'],1)">
					<input type="button" name="UnCheckAll" value="Uncheck All" onclick="checkAll(document.hfform['message_id[]'],0)">
					<select name="action">
						<option value="delete" selected>Delete</option>
						<?php
						if ($folder == "inbox") {
						echo '<option value="read">Mark as read</option><option value="unread">Mark as unread</option>';
						echo '<option value="pin">Pin</option><option value="unpin">Unpin</option>';
						}		
						?>				
					</select>
					<input type="submit" value="Do Selected">
					<?php
					if ($folder == "inbox") {
						echo '<input type="button" value="Go to Outbox" onclick="document.location = \'?h=mailbox&folder=outbox\'">';
					}
					else {
						echo '<input type="button" value="Go to Inbox" onclick="document.location = \'?h=mailbox&folder=inbox\'">';
					}
					?>	
					<input type="button" value="Write a new IM" onclick="redirect('?h=dowriteim');">
				</div>
			</div>
		</div>
	</form>	