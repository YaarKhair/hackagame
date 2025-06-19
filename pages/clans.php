<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!empty($_GET['showall'])) {
		$result = mysqli_query($link, "SELECT clan.id, clan.color, clan.alias, hacker.alias AS founder, hacker.id as founder_id FROM clan LEFT JOIN hacker ON clan.founder_id = hacker.id WHERE clan.active = 1 ORDER BY clan.alias ASC");
		$title = "All clans [<a href=\"?h=clans\">Show Active</a>]";
	}
	else {	
		$online = date($date_format, strtotime("-1 weeks"));
		$result = mysqli_query($link, "SELECT clan.id, clan.color, clan.alias, hacker.id as founder_id FROM clan LEFT JOIN hacker ON clan.founder_id = hacker.id WHERE clan.active = 1 AND clan.last_login > $online ORDER BY clan.alias ASC");
		$title = "Clans Active in the past week [<a href=\"?h=clans&showall=1\">Show All</a>]";
	}	
?>
	<h1><?php echo $title; ?></h1>
		<div class="row th light-bg">
			<div class="col w50">Clan</div>
			<div class="col w50">Founder</div>
		</div>
<?php
if (mysqli_num_rows($result) == 0) echo '<div class="row hr-light"><div class="col w100">No clans found</div></div>';
	else {
		$color = 0;	
		echo '<div class="dark-bg">';
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<div class="row hr-light"><div class="col w50"><span style="background-color:#'.$row['color'].';">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> '.ShowClanAlias($row['id']).'</div><div class="col w50">'.ShowHackerAlias($row['founder_id']).'</div></div>';
		}
		echo "</div>";
	}	
?>