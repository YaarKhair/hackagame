<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// online in the past 15 minutes
	$online = date($date_format, strtotime("-15 minutes"));
	if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) $result = mysqli_query($link, "SELECT id, alias, clan_id, clan_council, invisible FROM hacker WHERE last_click >= '".$online."' AND banned_date = '0' AND npc = 0 ORDER BY last_click DESC");
	else $result = mysqli_query($link, "SELECT id, alias, clan_id, clan_council, invisible FROM hacker WHERE last_click >= '".$online."' AND invisible = 0 AND banned_date = '0' AND npc = 0 ORDER BY last_click DESC");
?>
	<h1>Who's active?</h1>
	<div class="accordion">
    	<input id="active-15m" type="checkbox" class="accordion-toggle" checked>
        <label for="active-15m">Active in the past 15 minutes (<?php echo mysqli_num_rows($result); ?>)</label>
        <div class="accordion-box">
<?php
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['invisible'] == 1) { $pre = '<span style="text-decoration:line-through">'; $post = '</span>'; }
		else { $pre = ''; $post = ''; }
		echo $pre.ShowHackerAlias($row['id'], 0, true, true, true, true, true).$post.' | ';
	}
?>
		</div>
	</div>
<?php
	// online in the past 24h.
    include ("pages/active.php");
	
	// in hibernation
	$online = substr ($now, 0, 8)."000000";
	$result = mysqli_query($link, "SELECT id, alias, clan_id, clan_council FROM hacker WHERE hybernate_till > '".$now."' ORDER BY alias ASC");
?>
	
	<div class="row">
		<div class="col w50">
			<div class="accordion">
				<input id="hiber" type="checkbox" class="accordion-toggle">
				<label for="hiber">Hibernating (<?php echo mysqli_num_rows($result); ?>)</label>
				<div class="accordion-box">
<?php
	while ($row = mysqli_fetch_assoc($result)) {
		$color = false;
		// color clanmates in list
		if ($hackerdata['clan_id'] == $row['clan_id'] && $hackerdata['clan_id'] != 0) $color = true;
		echo ShowHackerAlias($row['id'],0,$color).' | ';
	}
?>
				</div>
			</div>
		</div>
		<div class="col w50">
<?php	
	ShowIcons();
	echo "</div></div>";
?>	
