<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$_SESSION['loaded'] = 0; // anti refresh
	
	$query = "SELECT * FROM filetransfer WHERE ready_date <> 0 AND ((source_id = ".$hackerdata['id']." AND source_entity = 'hacker') OR (destination_id = ".$hackerdata['id']." AND destination_entity = 'hacker'))";
	// any download in progress atm?
	$result = mysqli_query($link, "SELECT * FROM filetransfer WHERE ready_date <> 0 AND ((source_id = ".$hackerdata['id']." AND source_entity = 'hacker') OR (destination_id = ".$hackerdata['id']." AND destination_entity = 'hacker'))");

			echo '
					<h1>Active File Transfers</h1>
					<div class="row th">
						<div class="col w60">Details</div>
						<div class="col w20">Time Left</div>
						<div class="col w20">Action</div>
					</div>';
			$color = 0;
			if(mysqli_num_rows($result) > 0) {
				echo "<div class='light-bg'>";
				while ($row = mysqli_fetch_assoc($result)) {
					$color ++; if ($color ==2) $color = 0;						
					if ($color == 1) $bgcolor = "#000000";	else $bgcolor = "#181818";
					if ($row['source_id'] == $hackerdata['id'] && $row['source_entity'] == 'hacker') {
						$transfer = "Uploading";
						$target = "to ".$row['destination_ip'];
					}	
					else {
						$transfer = "Downloading";
						$target = "from ".$row['source_ip'];
					}	
					if (SecondsDiff($now, $row['ready_date']) > 0 ) $time = Seconds2Time(SecondsDiff($now, $row['ready_date']));
					else $time = "Checking file (CRC), please wait..."; //Seconds2Time(SecondsDiff("000000000000".date("s"), "00000000000060")); // the cronjob runs at the full minute
					echo '<div class="row hr-light"><div class="col w60">'.$transfer.'&nbsp;<strong>'.FileInfo($row['inventory_id'], "title").'</strong> ('.DisplaySize(FileInfo($row['inventory_id'], "size")).') '.$target.' </div><div class="col w20">'.$time.'</div><div class="col w20"><form class="small" action="index.php" method="POST"><input type="hidden" name="h" value="docanceltransfer"><input type="hidden" name="transfer_id" value="'.$row['id'].'"><input class="bg-red" type="submit" value="Cancel Transfer" title="Are you sure you want to cancel the transfer?"></form></div></div>';
				}
				echo '</div>';
			} else echo "<div class='row'>".PrintMessage("Info", "You have no pending transfers.").'</div>';
?>