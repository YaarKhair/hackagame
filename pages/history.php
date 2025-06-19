<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!empty($_GET['type'])) $type = sql($_GET['type']);
	
	$types = Array ("ep", "skill", "hp");
	if (!in_array($type, $types)) return "Invalid parameter.";
?>
		<h2><?php echo strtoupper($type); ?> history (last 25 lines and not older than <?php echo $log_keep; ?> days)</h2>
		<br>
		<?php PrintMessage("info", "This list is updated each 5 minutes."); ?>
		<br>
		<div class="row th light-bg">
			<div class="col w50"><?php echo strtoupper($type); ?></div>
			<div class="col w50">Date</div>
		</div>
<?php
		$query = "SELECT details, date FROM log WHERE event = '$type' AND hacker_id=".$hackerdata['id']." ORDER BY date DESC LIMIT 25";
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) { PrintMessage("info", "No records found."); }
		else {
			echo '<div class="dark-bg">';
			while ($row = mysqli_fetch_assoc($result)) {
				echo '
					<div class="row mv10">
						<div class="col w50">'.$row['details'].'</div>
						<div class="col w50">'.Number2Date($row['date']).'</div>
					</div>';
			}
			echo '</div>';
		}
?>