<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$searchterm = "";
	if (!empty($_GET['searchterm'])) $searchterm = sql($_GET['searchterm']);
	$show = "";
	if (!empty($_REQUEST['show'])) $show = sql($_REQUEST['show']);
	if ($show != "all" && $show != "my") $show = "all";
	
	if ($hackerdata['banned_date'] > 0) $show = "my"; // if you are banned you can only view your own tickets.

	if ($show == "all") $query = "SELECT ticket.*, ticket_type.type_desc, ticket_status.status, ticket_status.status_desc FROM ticket LEFT join ticket_type ON ticket.type_id = ticket_type.id LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE (ticket.title LIKE '%$searchterm%' OR ticket.message LIKE '%$searchterm%') AND respons_id = 0 AND ticket_type.id != 4 ORDER BY ticket_status.status ASC, DATE DESC LIMIT 0, 50";
	else $query = "SELECT ticket.*, ticket_type.type_desc, ticket_status.status, ticket_status.status_desc FROM ticket LEFT join ticket_type ON ticket.type_id = ticket_type.id LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE (ticket.title LIKE '%$searchterm%' OR ticket.message LIKE '%$searchterm%') AND hacker_id = ".$hackerdata['id']." AND respons_id = 0 ORDER BY DATE DESC LIMIT 0, 50";

	$result = mysqli_query($link, $query);

	echo '	[ <a href="?h=newticket">Open New Ticket</a> ]<form method="GET" action="index.php">
			<input type="hidden" name="h" value="tickets">
			<input type="hidden" name="show" value="'.$show.'">
			<input type="text" name="searchterm" maxlength="50">
			<input type="submit" value="Search!">
		</form>
		<h2>Tickets (50 most recent)</h2>
		<div class="row th light-bg">
			<div class="col w15">Title</div>
			<div class="col w15">Opened on</div>
			<div class="col w15">Opened by</div>
			<div class="col w15">Last reply</div>
			<div class="col w20">Type</div>
			<div class="col w20">Status</div>
		</div>
		';

		if (mysqli_num_rows($result) == 0) { echo '<div class="row">'.PrintMessage("info", "No tickets found.").'</div>'; }
		else {
			echo "<div class='dark-bg'>";
			$green = "BBFF6D";
			$blue = "95DBC9";
			$red = "FF3A3A";
			$statusbgcolor = "";
			$color = 0;
			while ($row = mysqli_fetch_assoc($result)) {
				if ($row['status'] == 1) $class = "red";
				if ($row['status'] == 2) $class = "blue";
				if ($row['status'] == 3) $class = "green";
				
				// Last poster
				$result2 = mysqli_query($link, "SELECT date, hacker_id FROM ticket WHERE respons_id = ".$row['id']." ORDER BY date DESC LIMIT 1");
				if (mysqli_num_rows($result2) == 0) {
					$last_date = "N/A";
					$last_poster = "N/A";
				}
				else {
					$row2 = mysqli_fetch_assoc($result2);
					$last_date = $row2['date'];
					$last_poster = $row2['hacker_id'];
				}	
				
				echo '
					<div class="row hr-light">
						<div class="col w15"><a href="?h=doreadticket&ticket_id='.$row['id'].'">'.$row['title'].'</div>
						<div class="col w15">'.Number2Date($row['date']).'</div>
						<div class="col w15">'.ShowHackerAlias($row['hacker_id'], 0, 0, 0, true, 0, 0).'</div>
						<div class="col w15">'.ShowHackerAlias($last_poster, 0, 0, 0, true, 0, 0).' @ '.Number2Date($last_date).'</div>
						<div class="col w20">'.$row['type_desc'].'</div>
						<div class="col w20"><strong>'.$row['status_desc'].'</strong></div>
					</div>';
			}
			echo "</div>";
		}
		
?>