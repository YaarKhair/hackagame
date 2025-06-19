<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	// Variables
	$get_id = intval($_GET['id']);
		
	// Checking if the user is banned
	$sql = mysqli_query($link, "SELECT banned_date FROM hacker WHERE id = $get_id");
	$row = mysqli_fetch_assoc($sql);
	if($row['banned_date'] == 0 && !InGroup($hackerdata['id'], 1)) return "This hacker is not banned!";
		
	// Query
	$inbox_query = mysqli_query($link, "SELECT im.id, im.title, im.date, hacker.alias FROM im LEFT JOIN hacker on sender_id = hacker.id WHERE im.reciever_id = $get_id ORDER BY im.date DESC");
	$row_inbox = mysqli_fetch_assoc($inbox_query);
		
	$outbox_query = mysqli_query($link, "SELECT im.id, im.title, im.date, hacker.alias FROM im LEFT JOIN hacker on sender_id = hacker.id WHERE im.sender_id = $get_id ORDER BY im.date DESC");
	$row_outbox = mysqli_fetch_assoc($outbox_query);
		
	$transaction_query = mysqli_query($link, "SELECT details, date FROM log WHERE hacker_id = $get_id AND event = 'bank' ORDER BY date DESC");
	$tran_row = mysqli_fetch_assoc($transaction_query);
		
	// Showing the stuffz.
		
		// Inbox table
				echo "<div class=\"col50left\">
					<table>
						<caption>Inbox</caption>
						<tr>
							<th>Subject</th>
							<th>From</th>
							<th>Date</th>
						</tr>";
			
    	if(mysqli_num_rows($inbox_query) > 0) {
			while($row_inbox = mysqli_fetch_assoc($inbox_query)) {
				
				echo "<tr>
						<td>{$row_inbox['title']}</td>
						<td>{$row_inbox['alias']}</td>
						<td>".Number2Date($row_inbox['date'])."</td>
						</tr>";
				}
    	}        
        else echo '<tr><td colspan="3">No messages</td></tr>';
        echo "</table></div>";
		
		// Outbox table			
		
				echo "<div class=\"col50right\">
					<table>
						<caption>Outbox</caption>
						<tr>
							<th>Subject</th>
							<th>To</th>
							<th>Date</th>
						</tr>";
			
    	if(mysqli_num_rows($outbox_query) > 0) {
			while($row_outbox = mysqli_fetch_assoc($outbox_query)) {
				
				echo "<tr>
						<td>{$row_outbox['title']}</td>
						<td>{$row_outbox['alias']}</td>
						<td>".Number2Date($row_outbox['date'])."</td>
						</tr>";
				}
    	}    
        else echo '<tr><td colspan="3">No messages</td></tr>';
		echo "</table></div>";
		
		echo "<br><br>";
		
		// Transactions
				echo "<table>
						<caption>Transactions</caption>
						<tr>
							<th>Date</th>
							<th style=\"text-align: right;\">Amount</th>
							<th>Details</th>
						</tr>";
			
    	if(mysqli_num_rows($transaction_query) > 0) {
			while($tran_row = mysqli_fetch_assoc($transaction_query)) {
				
				list($amount, $reason) = explode('|', $tran_row['details']); // details = amount|reason
				
				echo '
					<tr>
					<td>'.Number2Date($tran_row['date']).'</td>
					<td style="text-align: right;">'.number_format($amount).'</td>
					<td>'.$reason.'</td>
					</tr>';
				
		    }
    	}
      	else echo '<tr><td colspan="3">No transactions found..</td></tr>';
		echo "</table>";
?>