<div id="chat-wrapper">
<?php
	if ($hackerdata['chatkick_till'] > $now)
		return "You were kicked from chat and forums and are allowed to return in ".Seconds2Time(SecondsDiff($now, $hackerdata['chatkick_till']))."<br>Reason: ".$hackerdata['chatkick_reason'];
	
	// update last lastchatmsg_cate
	$result = mysqli_query($link, "UPDATE hacker SET lastchatmsg_date = '$now' WHERE id = {$hackerdata['id']}"); // else you will be /AFK the second you join
    
	include ("modules/permissions.php");
	if ($is_staff) $max_msg_length = 9001; // it's over 9000!
?>		
			<h1>Game & Support Chat</h1>
			<div class="col w75">
				<div id="chat-window">loading</div>		
				<div id="sender">
					<div class="row">
						<div class="col w85"><input type="text" name="msg" id="msg" maxlength="<?php echo $max_msg_length; ?>" class="w100i"/></div>
						<div class="col w15 right"><input type="submit" value="Say" id="chat-button" class="w100i"></div>
					</div>
				</div>
			</div>
			<div class="col w25">
				<div class="accordion">
					<input id="a1" type="checkbox" class="accordion-toggle" checked>
					<label for="a1">Currently chatting</label>
					<div class="accordion-box">
						<span id="users"></span>
					</div>
				</div>
				
				<div class="accordion">
					<input id="a2" type="checkbox" class="accordion-toggle">
					<label for="a2">Smilies</label>
					<div class="accordion-box">
					<?php echo PrintSmilies(); ?>
					</div>
				</div>

				<div class="accordion">
					<input id="a3" type="checkbox" class="accordion-toggle">
					<label for="a3">Commands</label>
					<div class="accordion-box">
						/ctf<br>
						/lastactive alias<br>
						/jail<br>
						/prison<br>
						/fbi<br>
						/status alias<br>
						/level alias<br>
						/clan alias<br>
						/jackpot<br>
						/wiki subject<br>
						/slap alias<br>
						/high5 alias<br>
						/afk<br>
						/bank<br>
						/ignore alias<br>
						/w|alias|message<br>
					</div>
				</div>
				
				<?php if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2) || InGroup($hackerdata['id'], 6)) { ?>
				<div class="accordion">
					<input id="a4" type="checkbox" class="accordion-toggle">
					<label for="a4">Staff Commands</label>
					<div class="accordion-box">
						<strong>/kick|alias|reason</strong><br>
						example:<br>/kick|chaozz|no flaming<br>
						<strong>/warn|alias|reason</strong><br>
						example:<br>/warn|chaozz|no swearing<br>
						<strong>/language</strong><br>
						Warns people to watch their language<br>
						<strong>/english</strong><br>
						Warns people the chat is english only
					</div>
				</div>
				<?php } ?>

			</div>
</div>