<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
	<h1>Invite friends, get cash!</h1>
	<p>HackerForever has a referral system which you can use to invite people to the game, giving us the chance to grow and giving you the chance to earn a little extra.<br>
	<br>
	For each friend that accepts your invite and then reaches level <?php echo $referral_level; ?> in the game, you will be awarded with <?php echo $referral_reward_ep; ?> EP and $<?php echo number_format($referral_reward_cash); ?>.<br>
	<br>
	There is no limit to how many people you can invite.</p>
	<br>

	<div class="accordion">
		<input id="referral" type="checkbox" class="accordion-toggle" checked>
		<label for="referral">Invite</label>
		<div class="accordion-box">
			
			<form action="index.php" method="POST" class="alt-design">
				<input type="hidden" name="h" value="doreferral">
				<div class="row">
					<div class="col w30"><label for="email">Email:</label></div>
					<div class="col w70"><input type="text" name="email" id="email"></div>
				</div>
				<div class="row">
					<div class="col w30"><label for="message">Message:</label></div>
					<div class="col w70"><textarea id="message" name="message" rows="10" cols="50"></textarea></div>
				</div>
				<div class="row">
					<div class="col w100"><input type="submit" value="Send invitation!"></div>
				</div>
			</form>
			
		</div>
	</div>