<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
		if (!HasInstalled($hackerdata['id'], "ACCESSLOGS")) return "No logging tool installed.";
		
		$log = '';
		if (!empty($_GET['log'])) $log = sql($_GET['log']);
		else return "Log not found.";
		
		$server_id = 0;
		if (!empty($_GET['server_id'])) {
			$server_id = intval($_GET['server_id']);
			if (GetServerOwner($server_id) != $hackerdata['id']) return "This is not your server.";
		}	

		if ($log != "transfer" && $log != "system" && $log != "server" && $log != "hack") return "Log not found.";
		
		$title = "Last 60 $log.log lines";
		if ($log == "server") {
			if ($server_id > 0) {
				$owner_id = mysqli_get_value ("hacker_id", "server", "id", $server_id);
				if ($owner_id != $hackerdata['id']) return "This is not your server!";
				$query = "SELECT date, details FROM log WHERE server_id = $server_id AND date <= '$now' AND deleted = 0 ORDER BY date DESC, id DESC LIMIT 60"; // specific server
			}
			else $query = "SELECT date, details FROM log WHERE server_id IN (SELECT id FROM server WHERE hacker_id = {$hackerdata['id']}) AND date <= '$now' AND deleted = 0 ORDER BY date DESC, id DESC LIMIT 60"; // all server
		}	
		else $query = "SELECT date, details FROM log WHERE hacker_id = {$hackerdata['id']} AND event = '$log' AND date <= '$now' AND deleted = 0 ORDER BY date DESC, id DESC LIMIT 60";

		$lines = '';
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 0) {
			$lines = 'Log empty.';
		}
		else {
			while($logdata = mysqli_fetch_assoc($result)) {
				$lines .= br2nl(Number2Date($logdata['date']).' | '.$logdata['details']."<br>"); //PHP_EOL;
			}
		}

		echo '<h1>'.$title.' (lines are deleted after '.$log_keep.' days)</h1>';
?>
	<div class="row">
    	<div class="col w80">
			<textarea class="w100i h450 monospace" readonly><?php echo $lines; ?></textarea>
		</div>
		<div class="col w20">
			<div class="accordion hr" id="admin-panel">
				<input class="accordion-toggle" type="checkbox" id="ac-logs" checked>
				<label for="ac-logs">Options</label>
				<div class="row accordion-box">
					<form action="#" method="post" class="alt-design">
						<input type="hidden" name="h" value="doclearlog">
						<input type="hidden" name="server_id" value="<?php echo $server_id; ?>">
						<input type="hidden" name="log" value="<?php echo $log; ?>">
						<input type="submit" class="bg-red" value="Clear"><br>
						<INPUT type="button" value="Go Back" onClick="location.href = '?h=logs';"><br>
					</form>
				</div>
			</div>
		</div>
	</div>