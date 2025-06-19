<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$error = "";
	if ($now < $hackerdata['nextethic_date']) $error = "You can only switch ethic once a day. You will be able to switch ethics in ".Seconds2Time(SecondsDiff($now, $hackerdata['nextethic_date']));
	
	if ($error == "") {
		if ($hackerdata['ethic_id'] == 0) PrintMessage ("Info", "Hi there newcomer. It's time to choose your hacker ethic. You can change this ethic daily by going to your profile and clicking the Change Ethic link.<br><br>Read the descriptions carefully and make your choice.", "40%");
		echo '
			<h1>Select your ethic (changeable every 24h!)</h1>
				<div class="row th">
					<div class="col w30">Ethic</div>
					<div class="col w70">Skills</div>
				</div>
				<div class="light-bg">
			<div class="row hr-light">
				<div class="col w30"><strong>WhiteHat</strong><br><img src="images/whitehat.png" title="Whitehat hacker" /></div>
				<div class="col w70">
					A WhiteHat hacker is a security expert and is specialized in securing systems.<br>
					<br>
					<br>
					<br>
					<form method="post" action="index.php">
						<input type="hidden" name="h" value="doselectethic" />
						<input type="hidden" name="ethic_id" value="3" />
						<input type="submit" value="Choose" />
					</form>			
				</div>
			</div>
			<div class="row hr-light">
				<div class="col w30"><strong>GreyHat</strong><br><img src="images/greyhat.png" title="Greyhat hacker" /></div>
				<div class="col w70">
					A GreyHat hacker is an allround expert and is skilled in both securing and compromising systems.<br>
					<br>
					<br>
					<br>
					<form method="post" action="index.php">
						<input type="hidden" name="h" value="doselectethic" />
						<input type="hidden" name="ethic_id" value="1" />
						<input type="submit" value="Choose" />
					</form>	
				</div>
			</div>
			<div class="row hr-light">
				<div class="col w30"><strong>BlackHat</strong><br><img src="images/blackhat.png" title="Blackhat hacker" /></div>
				<div class="col w70">
					A BlackHat hacker is a cracking expert and is specialized in compromising systems.<br>
					<br>
					<br>
					<br>
					<form method="post" action="index.php">
						<input type="hidden" name="h" value="doselectethic" />
						<input type="hidden" name="ethic_id" value="2" />
						<input type="submit" value="Choose" />
					</form>	
					</div>			
				</div>
			</div>
		</div>';
	}
	else {
		PrintMessage ("Error", $error, "40%");
	}
?>