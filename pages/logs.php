<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!HasInstalled($hackerdata['id'],"ACCESSLOGS")) return "No logging tool installed.";
	
	echo '
			<h1>Select A Log File</h1>
				<form action="index.php" method="GET" name="hf_form">
					<input type="hidden" name="h" value="readlog">
					<div class="row light-bg">
						<input type="radio" name="log" VALUE="system" id="system" checked> <label for="system">system.log</label><br>
						<input type="radio" name="log" VALUE="hack" id="hack"> <label for="hack">hack.log</label><br>
						<input type="radio" name="log" VALUE="transfer" id="transfer"> <label for="transfer">transfer.log</label><br>
						<input type="radio" name="log" VALUE="server" id="server"> <label for="server">server.log</label><br>
						<input type="submit" value="Open Log">
					</div>	
				</form>
		<script type="text/javascript">document.hf_form.code.focus();</script>';	
?>