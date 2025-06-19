<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
	<div class="row">
	<div class="col w40">
		<h1>Support Us!</h1>
<?php 	$mainpage = 1;
		include("./pages/moddonations.php");
?>
		<div id="merch" class="center">
			<img src="/images/the-official-hackerforevercom-t-shirt-you-get-to-look-cool-and-help-support-the-game.jpg" width="100%" height="100%" alt="Official Merchandise">
			<h3 class="mv10">Visit the store closest to your home!</h3>
			<a href="http://hackerforever.spreadshirt.com"><img src="theme/images/flag_us.png" alt></a><a href="http://hackerforever.spreadshirt.nl"><img src="theme/images/flag_eu.png" alt></a>
		</div>
		<div class="accordion">
			<input id="donors_list" type="checkbox" class="accordion-toggle" checked>
			<label for="donors_list">Premium Members</label>
			<div class="accordion-box">
				<?php
					// list of premium gamers
					$list = "";
					$result = mysqli_query($link, "SELECT id FROM hacker WHERE donator_till > '$now' AND donator = 1 ORDER BY donator_till DESC");
					if (mysqli_num_rows($result) > 0) {
						while ($row = mysqli_fetch_assoc($result)) $list .= ShowHackerAlias($row['id'])."<br>";
						echo $list;
					}
				?>
			</div>
		</div>
	</div>		
		<div class="col w60">
		<h1>Game News</h1>
        <a class="twitter-timeline" data-dnt="true" data-theme="dark" href="https://twitter.com/hackthegame?ref_src=twsrc%5Etfw">Tweets by hackthegame</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>		</div>
	</div>