<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// flood protection
	if ($hackerdata['nextim_date'] > $now && (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2))) 
		return "Flood Protection!<br>You need to wait another ".SecondsDiff($now, $hackerdata['nextim_date'])." seconds before sending another IM.";
	
	// in a clan?
	if ($hackerdata['clan_id'] == 0) 
		return "You are not in a clan.";
	
	// this function is for council members only
	if ($hackerdata['clan_council'] == 0) 
		return "You are not part of the clan council.";

	if (!IsOnlineServer(GetGateway($hackerdata['clan_id']))) 
		return "Your gateway is currently offline.";
?>	
		<h2>Message All Clan Members</h2>
			<div class="row">
				<form method="POST" action="index.php" name="hf_form">
					<input type="hidden" name="h" value="dosendim">
					<input type="hidden" name="clanim" value="1">
					<input type="text" name="title" class="icon subject w100i" id="subject" placeholder="Subject">
					To:  <select name="clan_group"><option value="1">Every member</option><option value="2">Council members only</option><option value="3">Non-council members only</option></select><br>
					<input type="checkbox" name="onlineonly" id="onlineonly"><label for="onlineonly">Online members only</label><br>
					<input type="checkbox" name="copy2self" id="copy2self"><label for="copy2self">Send a copy to myself</label><br>
					<textarea name="message" id="message" class="w100i h350" placeholder="Message"></textarea><br>
					<input type="submit" value="Send Message">
				</form>	
			</div>
		</tbody>
	</table>		
	<script type="text/javascript">document.hf_form.title.focus();</script>