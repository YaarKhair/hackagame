<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$show = 50;
	$result = mysqli_query($link, "SELECT id, alias, banned_date, banned_reason FROM hacker WHERE banned_date > 0 ORDER BY banned_date DESC LIMIT $show");
	
	echo '<h1>Hall Of Shame ('.$show.' Most Recent Bans)</h1>
		<div class="row th light-bg"><div class="col w25">Alias</div><div class="col w25">Ban date</div><div class="col w50">Ban reason</div></div>
		<div class="dark-bg">';
			
	while ($row = mysqli_fetch_assoc($result)) {
		echo '<div class="row hr-light"> ';
		echo '<div class="col w25"><a href="?h=profile&id='.$row['id'].'">'.$row['alias'].'</a></div>';
		echo '<div class="col w25">'.Number2Date($row['banned_date']).'</div>';
		echo '<div class="col w50">'.MaskIP($row['banned_reason']).'</div>';
		echo '</div>';
	}
	echo "</div>";
?>
