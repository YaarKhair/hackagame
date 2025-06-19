<?php if (!isset($index_refer) || $index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php	
	$result = mysqli_query($link, "SELECT id, country, started, hackpoints FROM hacker WHERE active = 1 AND npc = 0 AND banned_date = 0 AND alias <> '' AND network_id = 2 AND clan_id <> $staff_clanid ORDER BY hackpoints DESC");
	$number = mysqli_num_rows($result);
	$output = '
			<h1>World Rank List - Sorted by Hack Points</h1>
				<div class="row th">
					<div class="col w10">#</div>
					<div class="col w30">Alias</div>
					<div class="col w20">Hack Points</div>
					<div class="col w20">Joined</div>
					<div class="col w20">Country</div>
				</div>';
	if (mysqli_num_rows($result) > 0) {		
		$output .= "<div class='light-bg'>";
		$worldrank = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			if (!InStaff($row['id'])) {
				$worldrank ++;
				$output .= '<div class="row hr-light">';
				$output .= '<div class="col w10"><a name="'.$worldrank.'">'.$worldrank.'</a></div>';
				$output .= '<div class="col w30">'.ShowHackerAlias($row['id'], 0, false, true).'</a></div>';
				$output .= '<div class="col w20">'.$row['hackpoints'].'</a></div>';
				$output .= '<div class="col w20">'.Number2Date($row['started']).'</div>';
				$output .= '<div class="col w20"><img src="./images/flags/'.$row['country'].'.png"></div>';
				$output .= '</div>';
			}	
		}
		$output .= "</div>";
	}	

	$output .= '<div class="right">Generated@'.Number2Date($now).'<br>';
	$output .= 'Next@'.Number2Date(date($date_format, strtotime("+24 hour"))).'</div>';
	
	$myFile = "$gamepath/pages/worldrankhp.php";
	$fh = fopen($myFile, 'w') or die();
	fwrite($fh, $output);
	fclose($fh);
?>
