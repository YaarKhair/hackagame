<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// show bounties
	echo '
	<img src="images/bountyboardlogo.png" title="Bounty Board"><br>
	<h1>Current bounties</h1>
	The first hacker that executes the desired tool on the victim will receive the reward.<br>It will be anonymous. The contractor will not see your name.<br>
	<br>
		<div class="row th light-bg">
			<div class="col w20">Victim</div>
			<!--<div class="col w10">Level</div>//-->
			<div class="col w20">Tool</div>
			<div class="col w20">Contractor</div>
			<div class="col w20">Added</div>
			<div class="col w20">Reward</div>
		</div>
		<div class="dark-bg">
	';	
	$result = mysqli_query($link, "SELECT bounty.*, product.title, product.image FROM bounty LEFT JOIN product ON bounty.tool_id = product.id WHERE bounty.date < '$now'");
	if (mysqli_num_rows($result) == 0) {
		echo '<div class="row">List is empty...</div>';
	}
	else {
		$size = 50;
		while ($row = mysqli_fetch_assoc($result)) {
			// Getting the level of the victim
			$victim_level = EP2Level(mysqli_get_value("ep","hacker","id",$row['victim_id']));
			$valid_date = date($date_format, strtotime($row['date']." + $bounty_days days"));
			// Adding an [X] if the bounty is yours so you can delete it
			$pre = '';
			if($hackerdata['id'] == $row['contracter_id']) $pre = "[<a href='?h=dobountyboard&action=delete&id={$row['id']}'><span class='red'>X</span></a>]";
			if ($row['anonymous'] == 0) $contracter = ShowHackerAlias($row['contracter_id'], 0).'<br>'.ShowAvatar($row['contracter_id'], $size, '', 'hacker', false, 'size-down frame rounded'); // .'<br>'.ShowAvatar($row['contracter_id'])
			else $contracter = "**ANONYMOUS**".'<br>'.ShowAvatar(0, $size, '', 'hacker', false, 'size-down frame rounded'); // .'<br>'.ShowAvatar(0)
			echo '<div class="row hr-light"><div class="col w20">'.$pre.ShowHackerAlias($row['victim_id'], 0).'<br>'.ShowAvatar($row['victim_id'], $size, '', 'hacker', false, 'size-down frame rounded').'</div>'; // .'<br>'.ShowAvatar($row['victim_id'])
			//echo '<div class="col w10">'.$victim_level.'</div>';
			echo '<div class="col w20">'.$row['title'].'<br><img src="images/'.$row['image'].'" title="'.$row['title'].'" width="'.$size.'" height="'.$size.'" class="rounded"></div>'; // <br><img src="images/'.$row['image'].'" title="'.$row['title'].'">
			echo '<div class="col w20">'.$contracter.'</div>';
			echo '<div class="col w20">'.Number2Date($row['date']).'</div>';
			echo '<div class="col w20">'.$currency.number_format($row['reward']).'</div></div>';
		}
	}
?>
	</div>
	<br>
	<br>
	<p>
		<img src="images/bountyboard.png" width="33%" align="right" valign="top">
		<h2>Add a hacker to the Bounty Board</h2>
		Here are the rules:
		<ul>
			<li>As soon as you add a hacker to the bounty board, the reward will be taken from your bank account.</li>
			<li>Placing a bounty is free, unless you choose anonymous. Then you pay a fee of <?php echo $currency.number_format($bounty_anonymous); ?></li>
			<li>Unless a bounty is claimed, it will be removed after one week, without refunds.</li>
			<li>The reward must be at least twice the price of the selected tool.</li>
			<li>You will not know who claimed your bounty.</li>
			<li>Choosing Anonymous when adding a contract will not reveal your name in the contractor field.</li>
		</ul>	
		<form method="post" action="index.php">
			<input type="hidden" name="h" value="dobountyboard">
			<input type="hidden" name="action" value="add">
			Victim: <input type="text" name="victim"><br>
			Tool: <select name="tool_id">
			<?php
		$result = mysqli_query ($link, "SELECT id, title FROM product WHERE level > 0 AND code = 'PCHACK'"); // level > 0 to exclude the noobtool
		while ($row = mysqli_fetch_assoc($result))
			echo '<option value="'.$row['id'].'">'.$row['title'];
			?>
			</select><br>
			Bounty: <input type="text" name="reward"><br>
			<input type="checkbox" name="anonymous" id="anonymous"><label for="anonymous">Anonymous</label><br>
			<input type="submit" value="Add bounty">
		</form>	
	</p>