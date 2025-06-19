<?php
	include 'modules/permissions.php';
	
	// Set up the types and the query
	$extra_query = "WHERE private = 0 ORDER BY date DESC";
	if($is_staff) $extra_query = 'ORDER BY date DESC';
	
	// Pagination
	$page = 1;
	if(isset($_GET['page']) && $is_staff) $page = intval($_GET['page']);
	$extra_query .= " LIMIT ".(($page - 1) * $display_logs).", $display_logs";
	
	// Get the information from the database and display it
	$result = mysqli_query($link, "SELECT ticket_id, date, details, type, id, private, hacker_id FROM changelog $extra_query") or die(mysqli_error($link));
	$changelog = array();
	while($row = mysqli_fetch_assoc($result)) 
		$changelog[] = array("id" => $row['id'], "date" => $row['date'], "type" => $row['type'], "details" => $row['details'], "hacker_id" => $row['hacker_id'], "private" => $row['private'], "ticket_id" => $row['ticket_id']); 
		
	// Total count of records
	$result = mysqli_query($link, "SELECT COUNT(*) AS total FROM changelog");
	$row = mysqli_fetch_assoc($result);
	$total = $row['total'];
	
	// Tickets
	$tickets_result = mysqli_query($link, "SELECT ticket.id, ticket.title FROM ticket LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE ticket_status.status = 3 AND respons_id = 0 ORDER BY ticket.reply_date DESC LIMIT 0,5");
	$tickets = array();
	$tickets[0] = "No ticket";
	while($ticket_row = mysqli_fetch_assoc($tickets_result)) $tickets[$ticket_row['id']] = $ticket_row['title'];
	
	// Update your last visit of the changelog
	$result = mysqli_query($link, "UPDATE hacker SET lastchangelogvisit_date = '$now' WHERE id = {$hackerdata['id']}");
?>
<!--- Display changelog --->
	<h1>Changelog</h1>
	<form action="index.php" method="POST">
	<input type="hidden" name="h" value="dochangelog">
	<input type="hidden" name="action" value="delete">
	<div class="row th light-bg">
		<div class="col w25">Date</div>
		<div class="col w10">Added By</div>
		<div class="col w10">Type</div>
		<div class="col w55">Details</div>
	</div>
	<div class="dark-bg">
	<?php 
	foreach($changelog as $entry) {
		echo "<div class='row hr-light'>";
		echo "<div class='col w25'>";
		if($is_staff) echo "<input type='checkbox' value='{$entry['id']}' name='log_id[]' id='log_{$entry['id']}'><label for='log_{$entry['id']}'>&nbsp;&nbsp;</label>";
		
		// status
		if($entry['private'] == 1) $private = '<span class="red">[PRIVATE]:</span> ';
		else $private = '';
		
		// Link
		$linked_ticket = '';
		if($entry['ticket_id'] != 0)
			$linked_ticket = "&nbsp;[<a href='index.php?h=doreadticket&ticket_id={$entry['ticket_id']}'>#{$entry['ticket_id']}</a>]";

		echo Number2Date($entry['date'])."</div>";
		echo "<div class='col w10'>".ShowHackerAlias($entry['hacker_id'], false, false, false)."</div>";
		echo "<div class='col w10'>".$changelog_types[$entry['type']]."</div>";
		echo "<div class='col w55'>".$private.$entry['details'].$linked_ticket."</div>";
		echo "</div>";
	}
	?>
	</div>
	<?php 
	if($is_staff) {
		echo "<input type='submit' value='Delete'><br>Page: ";
		for($i = 1; $i <= ceil($total / $display_logs); $i++) echo "<a href='?h=changelog&page=$i'>$i</a>&nbsp;&nbsp;";
	}
	?>
	</form>
	<br><br>
<!--- Add change logs ---->
<?php if($is_staff) { ?>
		<h2>Add entry</h2>
		<form action="index.php" method="POST">
		<input type="hidden" name="h" value="dochangelog">
		<input type="hidden" name="action" value="add">
		<div class="row">
			<div class="col w30">Private</div>
			<div class="col w70"><input type="checkbox" name="private" value="on" id="private"><label for="private"></label></div> 
		</div>
		<div class="row">
			<div class="col w30">Type</div>
			<div class="col w70"><select name="type"><option value="1">Bug Fix</option><option value="2">Feature</option><option value="3">Tweak</option></select></div> 
		</div>
		<div class="row">
			<div class="col w30">Details</div>
			<div class="col w70"><input type="text" name="details" class="w100i"></div>
		</div>
		<div class="row">
			<div class="col w30">Ticket</div>
			<div class="col w70"><select name="ticket_id"><?php foreach($tickets as $id => $text) echo "<option value='$id'>$text</option>"; ?></select></div>
		</div>
		<div class="row"><div class="col w100"><input type="submit" value="Add"></div></div>
		</form>
<?php } ?>
	
	