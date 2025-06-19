<?php If(!InGroup($hackerdata['id'], 1)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load", $now); die(); } ?>
<?php
	// Put the groups in an ID => name array
	$extra_groups = array();
	$group_result = mysqli_query($link, "SELECT id, name FROM permgroup ORDER BY id ASC");
	while($group_row = mysqli_fetch_assoc($group_result)) $extra_groups[$group_row['id']] = $group_row['name'];
		
	// HTTP Headers
	$base64 = $row['http_header'];
	$http_headers = unserialize(base64_decode($row['http_header']));
	$http_result = mysqli_query($link, "SELECT alias, id FROM hacker WHERE http_header = '$base64' AND id != {$row['id']} AND LENGTH(http_header) > 0 ORDER BY last_click DESC");
	$similar_header = '';
	while($http_row = mysqli_fetch_assoc($http_result)) $similar_header .= "<a href='index.php?h=profile&id={$http_row['id']}'>{$http_row['alias']}</a><br>";
	if(mysqli_num_rows($http_result) == 0) $similar_header = 'None found.';
	$http_headers['Similar Headers'] = $similar_header;
	
?>
<!---- Left column ---> 
<div class="accordion">
	<input id="a1" type="checkbox" class="accordion-toggle">
    <label for="a1">Admin options</label>
	<div class="accordion-box">
		<div class="row">
			<div class="col w33">
				<!---- Extra group form ---->
					<h3>Extra Group:</h3>
					<form method="POST" action="index.php" class="small">
						<input type="hidden" name="h" value="doadminaction">
						<input type="hidden" name="action" value="dogroup">
						<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
						<select name="group_id">
							<?php foreach($extra_groups as $group_id => $group_name) echo "<option value='$group_id'>$group_name</option>"; ?>
						</select>
						<select name="addremove">
							<option value="1">Add</option>
							<option value="2">Remove</option>
						</select>	
						<input type="submit" value="Submit">
					</form>
			</div>	
			<div class="col w33">
				<h3>Premium Member</h3>
					<form method="POST" action="index.php" class="small">
					<input type="hidden" name="h" value="doadminaction">
					<input type="text" name="amount" placeholder="Amount donated: €"><br>
					<input type="text" name="periods" placeholder="Periods (1 period = 6 months)"><br>
					<input type="hidden" name="action" value="makepremium">
					<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
					<input type="submit" value="Make Premium Member">
					<?php if($row['donator_till'] > 0) echo "Donator until ".Number2Date($row['donator_till']); ?>
					</form>
			</div>
			
			<div class="col w33">
				<h3>White/blacklist</h3>
					<form method="POST" action="index.php" class="small">
						<input type="hidden" name="h" value="doadminaction">
						<select name="action">
							<option value="whitelist">Whitelist</option>
							<option value="blacklist">Blacklist</option>
						</select>
						<input type="hidden" name="ip" value="<?php echo $row['real_ip']; ?>">
						<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
						<input type="text" name="reason" value="[reason]">
						<input type="submit" value="Whitelist / Blacklist">
					</form>
			</div>
		</div>
		
<!--		<h3>HTTP Header</h3>
		<div class="row light-bg">
				<?php 
					/*if($http_headers) {
						foreach($http_headers as $header => $value) {
							if($header == 'Similar Headers') echo "<div class='col w30'>$header</div><div class='col w70'>$value</div>";
							else echo "<div class='col w30'>$header</div><div class='col w70'>".wordwrap($value, 80, "<br>", true)."</div>";
						}
					} else echo "<div class='row'><div class='col w100'>None recorded.</div></div>";*/
				?>
		</div>//-->
		<h3>Immitate</h3>
		<div class="row">
			<form method="POST" action="index.php" class="small">
				<input type="hidden" value="doadminimmitate" name="h">
				<input type="hidden" name="hacker_id" value="<?php echo $row['id']; ?>">
				<input type="hidden" name="action" value="immitate">
				<input type="submit" value="Immitiate">
			</form>
		</div>
	</div>
</div>

	