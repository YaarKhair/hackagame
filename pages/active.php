<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    $online = date($date_format, strtotime("-24 hours"));
    $result = mysqli_query($link, "SELECT id, alias, clan_id, clan_council FROM hacker WHERE last_click >= '".$online."' AND banned_date = '0' AND npc = 0 AND invisible = 0 ORDER BY last_click DESC");
	
?>
	<div class="accordion">
    	<input id="active-24h" type="checkbox" class="accordion-toggle">
        <label for="active-24h">Active in the past 24 hours (<?php echo mysqli_num_rows($result); ?>)</label>
        <div class="accordion-box">
<?php
	while ($row = mysqli_fetch_assoc($result)) {
		$color = false;
		// color clanmates in list
		if ($hackerdata['clan_id'] == $row['clan_id'] && $hackerdata['clan_id'] != 0) $color = true;
		echo ShowHackerAlias($row['id'], 0, $color, true, true, true, false).' | ';
	}
?>
        </div>
   </div>
