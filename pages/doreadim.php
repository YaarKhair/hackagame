<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$id = 0;
	if (!empty($_GET['id'])) $id = intval($_GET['id']);
	
	if ($id < 1) return "Invalid message ID";
	
	// now read the message
	$query = "SELECT im.*, hacker.alias FROM im LEFT JOIN hacker ON im.sender_id = hacker.id WHERE im.date <= ".$now." AND im.id = ".$id;
	$result = mysqli_query($link, $query);
	
	
	if (mysqli_num_rows($result) == 0) return "Invalid message ID";

	$row = mysqli_fetch_assoc($result);
	if ($row['reciever_id'] != $hackerdata['id'] && $row['sender_id'] != $hackerdata['id']) {
		AddLog ($hackerdata['id'], "hacker", "staff", "Tried to open an IM that is not send to or by him, with ID ".$id, $now);
		return "It is not polite to open other peoples mail.";
	}
	
	$_SESSION['loaded'] = 0; // anti refresh (swiss bank)
	$message = replaceBBC(stripslashes($row['message']));			
 
?>
	<h1><?php echo stripslashes($row['title']); ?></h1>
	<div class="row">
		<div class="col w70">
			<div class="w100i dark-bg">
			<div class="mb10 hr"><h3><?php echo 'On '.Number2Date($row['date']).' '.ShowHackerAlias($row['sender_id']).' wrote:'; ?></h3></div>
			<?php echo $message; ?>
			</div>
		</div>
		<div class="col w30">
			<div class="accordion hr">
				<input class="accordion-toggle" type="checkbox" id="ac-bbtags" checked>
				<label for="ac-bbtags">Message Options</label>
				<div class="row accordion-box">
					<?php if($row['sender_id'] != $hackerdata['id']) { ?>
					<form action="index.php" method="POST" class="alt-design">
						<input type="submit" value="Reply">
						<input type="hidden" name="h" value="dowriteim">
						<input type="hidden" name="reply_id" value="<?php echo $row['id']; ?>">
					</form>
					<?php } ?>
					<form class="alt-design">
					<input type="button" value="Return to inbox" onclick="document.location = '?h=mailbox&folder=inbox';">
					</form>
					<form action="index.php" method="POST" class="alt-design">
						<input type="submit" class="bg-red" value="Delete" disabled>
						<input type="hidden" name="h" value="doim">
						<input type="hidden" name="action" value="delete">
						<input type="hidden" name="message_id[]" value="<?php echo $row['id']; ?>">				
					</form>
				</div>
			</div>
		</div>
	</div>
	
<?php
	// set message to read if you are the reciever
	if ($row['reciever_id'] == $hackerdata['id']) $query = "UPDATE im SET unread = 0 WHERE reciever_id = ".$hackerdata['id']." AND id = ".$id;
	$result = mysqli_query($link, $query);
?>
