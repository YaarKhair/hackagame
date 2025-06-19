<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// flood protection
	if ($hackerdata['nextim_date'] > $now && $hackerdata['donator_till'] < $now && (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2))) 
		return "Flood Protection!<br>You need to wait another ".SecondsDiff($now, $hackerdata['nextim_date'])." seconds before sending another IM.";
	
	$username = '';
	$title = '';
	$message = '';
	
	if (!empty($_REQUEST['username'])) $username = sql($_REQUEST['username']); // send im via profile
	
	$reply_id = 0;
	if (!empty($_POST['reply_id'])) $reply_id = intval($_POST['reply_id']);
	
	if ($reply_id != 0) {
		$result = mysqli_query($link, "SELECT im.*, hacker.alias FROM im LEFT JOIN hacker on im.sender_id = hacker.id WHERE im.id = ".$reply_id." AND reciever_id = ".$hackerdata['id']);
		if (mysqli_num_rows($result) != 0) {
			$row = mysqli_fetch_assoc($result);
			$username = $row['alias'];
			$title = "RE: ".stripslashes($row['title']);	
			$message = br2nl('<br><br><br>On '.Number2Date($row['date']).' '.$username.' wrote:<br>------------------------<br>'.stripslashes(strip_tags($row['message'], "<br><br>" )));	
		}
	}
?>	
	<h1>Write a new message</h1>
	<div class="row">
		<div class="col w70">
			<form method="POST" action="index.php" name="hf_form">
				<input type="hidden" name="h" value="dosendim">
				<input type="text" class="icon user w100i" id="to"  placeholder="hacker1; hacker2; hacker3 (5 recipients max)" name="username" <?php if(!empty($username)) echo "value='$username'"; ?>>
				<input type="text" name="title" class="icon subject w100i" id="subject" placeholder="Subject" <?php if(!empty($title)) echo "value='$title'"; ?>>
				<textarea name="message" id="message" class="w100i h350" placeholder="Message"><?php if(!empty($message)) echo $message; ?></textarea><br>
				<input type="submit" value="Send message">
			</form>
		</div>
		<div class="col w30">
			<div class="accordion hr">
            	<input class="accordion-toggle" type="checkbox" id="ac-bbtags" checked>
            	<label for="ac-bbtags">Text formatting</label>
            	<div class="row accordion-box">

			<?php PrintBBC("100%"); ?>
				</div>
			</div>
		</div>
	</div>
<?php
	if ($reply_id == 0) echo '<script type="text/javascript">document.hf_form.username.focus();</script>';
	else echo '<script type="text/javascript">document.hf_form.message.focus();</script>';
?>	