<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2) && !InGroup($hackerdata['id'], 6)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$status_id = 0;
	if (!empty($_GET['status_id'])) $status_id = intval($_GET['status_id']);

	$page = 0;
	if (!empty($_GET['page'])) $page = intval($_GET['page']);
	$perpage = 50;
	$page = $page * $perpage;
	
	// supporters can not see MOD ONLY tickets
	if (InGroup($hackerdata['id'], 6)) $query = "SELECT ticket.*, ticket_type.type_desc, ticket_status.status, ticket_status.status_desc FROM ticket LEFT JOIN ticket_type ON ticket.type_id = ticket_type.id LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE ticket_status.status = $status_id AND ticket.respons_id = 0 AND ticket.type_id <> 4 ORDER BY reply_date DESC LIMIT $page, $perpage";
	else $query = "SELECT ticket.*, ticket_type.type_desc, ticket_status.status, ticket_status.status_desc FROM ticket LEFT JOIN ticket_type ON ticket.type_id = ticket_type.id LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE ticket_status.status = $status_id AND ticket.respons_id = 0 ORDER BY reply_date DESC LIMIT $page, $perpage";

	$result = mysqli_query($link, $query);

	echo '
		<h2>Tickets</h2>
		<div class="row th">
			<div class="col w15">Title</div>
			<div class="col w15">Opened on</div>
			<div class="col w15">Opened by</div>
			<div class="col w15">Last reply</div>
			<div class="col w20">Type</div>
			<div class="col w20">Status</div>
		</div>
			<form method="POST" action="index.php" name="hfform">
				<input type="hidden" name="h" value="doticket">
				<input type="hidden" name="action" value="instaclose">';

		if (mysqli_num_rows($result) == 0) echo '<div class="row">'.PrintMessage("Info", "No tickets found.").'</div>';
		else {
			echo "<div class='light-bg'>";
			$class = "";
			while ($row = mysqli_fetch_assoc($result)) {
				$instaclose = "";
				if ($row['status'] == 1) { 
					$class = "red";
					$instaclose = '<input type="checkbox" name="ticket_id[]" value="'.$row['id'].'">';
				}	
				if ($row['status'] == 2) $class = "blue";
				if ($row['status'] == 3) $class = "green";
				
				echo '
					<div class="row hr-light">
						<div class="col w15">'.$instaclose.'&nbsp;<a href="?h=doreadticket&ticket_id='.$row['id'].'">'.$row['title'].'</a></div>
						<div class="col w15">'.Number2Date($row['date']).'</div>
						<div class="col w15">'.ShowHackerAlias($row['hacker_id'], 0, 0, 0, true, 0, 0).'</div>
						<div class="col w15">'.ShowHackerAlias($row['reply_hacker_id'], 0, 0, 0, true, 0, 0).'<br>'.Number2Date($row['reply_date']).'</div>
						<div class="col w20">'.$row['type_desc'].'</div>
						<div class="col w20">'.$row['status_desc'].'</div>
					</div>';
			}
			echo "</div>";
		}
		echo '
			<input type="button" name="CheckAll" value="Check All" onclick="checkAll(document.hfform[\'ticket_id[]\'],1)">
			<input type="button" name="UnCheckAll" value="Uncheck All" onclick="checkAll(document.hfform[\'ticket_id[]\'],0)">
			<input type="submit" value="InstaClose Selected">					
		</form>';
					
		$result = mysqli_query($link, "SELECT count(ticket.id) as NumRows FROM ticket LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE ticket_status.status = $status_id AND respons_id = 0");
		$row = mysqli_fetch_assoc($result);
		$numtickets = $row['NumRows'];
		if ($numtickets > $perpage) {
			echo "Page: ";
			$page = 0;
			for ($i = 0; $i < $numtickets; $i+=$perpage) {
				echo '[<a href="?h=modtickets&status_id='.$status_id.'&page='.$page.'">'.$page.'</a>]';
				$page ++;
			}
		}	
?>		
