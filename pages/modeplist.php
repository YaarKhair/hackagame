<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$date = date($date_format, strtotime("-24 hours"));
 	$result = mysqli_query($link, "SELECT alias, hacker_id, sum(substring_index(TRIM(LEADING 'Ep increased: ' FROM details), ' ', 1)) as epgain FROM log LEFT JOIN hacker ON log.hacker_id = hacker.id WHERE event = 'ep' AND date > '$date' group by hacker_id order by epgain DESC");
 	echo '
			<h2>Total EP gained in the last 24 hours</h2>
			<div class="row th">
				<div class="col w50">Hacker</div>
				<div class="col w50">Total EP</div>
			</div>
			<div class="light-bg">'; 	
			
 	while ($row = mysqli_fetch_assoc($result)) {
 		// print details
 		echo "<div class='row hr-light'><div class='col w50'><a href=\"?h=modinterval&id={$row['hacker_id']}\">{$row['alias']}</a></div><div class='col w50'>{$row['epgain']}</div></div>";
 	}
	echo '</div>';
?>
