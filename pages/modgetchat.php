<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    // Not an admin or mod?
    $admin = InGroup($hackerdata['id'], 1);
   	$mod = InGroup($hackerdata['id'], 2);
   	if(!$admin && !$mod) return 'Session error.';

    // Get the limit
    $limit = 200;
    if(!empty($_REQUEST['limit'])) $limit = intval($_REQUEST['limit']);

   	// Get start date and end date
    $start_date = '0';
   	if(!empty($_REQUEST['start_date'])) {
        $start_date = sql($_REQUEST['start_date']);
        $start_date = date('YmdHis',strtotime($start_date));
   	}
    
   	$end_date = '999999999999999';
   	if(!empty($_REQUEST['end_date'])) {
       $end_date = sql($_REQUEST['end_date']);
       $end_date = date('YmdHis',strtotime($end_date));
   	} 
    
   	// Get the alias
   	$alias = '';
   	if(!empty($_REQUEST['alias'])) $alias = sql($_REQUEST['alias']);

    // Prepare the query
   	$clause = "AND date >= '$start_date' AND date <= '$end_date'";
    if(!empty($alias)) $clause .= " AND sender_alias = '$alias'";
    $clause .= " ORDER BY id DESC LIMIT $limit";
    
   	$result = mysqli_query($link, "SELECT sender_id, sender_alias, sender_color, message, message_color, date, id FROM chat WHERE receiver_id = 0 AND deleted <> 1 $clause");

    // Variables
    $aliaspre = '<strong>&lt;';
	$aliaspost = '&gt;</strong>';
	$timecolor = '6E6E6E';
	$timepre = '[';
	$timepost = ']';

	// Table html
	echo '<form action="index.php" method="POST">';
	echo '<input type="hidden" name="h" value="moddeletechat"/>';
	echo '<div class="row th">';
	echo '<div class="col w5">Remove</div>';
	echo '<div class="col w95">Messages</div>';
	echo '</div>';
	$chat = array();
	while ($row = mysqli_fetch_assoc($result)) {
        $time = date('H:i',strtotime($row['date']));
        $message = ReplaceBBC($row['message']);
        $message_color = $row['message_color'];            
        $sender_alias = $row['sender_alias'];
        $sender_color = $row['sender_color'];            
        $chat[] = '<div class="row light-bg"><div class="col w5"><input id="'.$row['id'].'" type="checkbox" name="id[]" value="'.$row['id'].'"/><label for="'.$row['id'].'">&nbsp;</label></div><div class="col w95"><span style="color:#'.$timecolor.'">'.$timepre.$time.$timepost.'</span>&nbsp;<span style="color:#'.$sender_color.'">'.$aliaspre.$sender_alias.$aliaspost.'</span>&nbsp;<span style="color:#'.$message_color.'">'.$message.'</span></div></div>';
    }
    $chat = array_reverse($chat);
    foreach($chat as $line) echo $line;
    echo '<br><br>';
    echo '<input type="submit" value="Delete"/>';
    echo '</form><br><br>';
?>