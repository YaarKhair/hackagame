<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include('modules/permissions.php');

	// Chatkick
	if ($hackerdata['chatkick_till'] > $now) return "You were kicked from chat and forums and are allowed to return in ".Seconds2Time(SecondsDiff($now, $hackerdata['chatkick_till']));

	// Internal actions
	$actions = array("list_boards", "list_threads", "view_thread");
	$action = 'list_boards';	// default
	
	// Clan ID
	$clan_id = 0;
	if(!empty($_REQUEST['clan_id']) && $_REQUEST['clan_id'] > 0) {
		$clan_id = intval($_REQUEST['clan_id']);
		$action = 'list_boards';
	}
	
	// Board ID
	$board_id = 0;
	if(!empty($_REQUEST['board_id']) && $_REQUEST['board_id'] > 0) {
		$board_id = intval($_REQUEST['board_id']);
		$action = "list_threads";
	}
	
	// Thread ID
	$thread_id = 0;
	if(!empty($_REQUEST['thread_id']) && $_REQUEST['thread_id'] > 0) {
		$thread_id = intval($_REQUEST['thread_id']);
		$action = 'view_thread';
	}
		
	// Actions
	if($action == "list_boards") {
	
		// Checks
		if($clan_id > 0 && $clan_id != $hackerdata['clan_id'] && !$is_staff) return "You cannot view this board.";
		
		// Page title
		$page_title = "Public";
		if($clan_id > 0) $page_title = mysqli_get_value("alias", "clan", "id", $clan_id);
				
		// Query
		$result = mysqli_query($link, "SELECT board.*, COUNT(thread.id) as count FROM board LEFT JOIN thread ON thread.board_id = board.id WHERE board.clan_id = $clan_id GROUP BY board.id");
		$data = array();
		$count = 0;
		while($row = mysqli_fetch_assoc($result)) {
			$data[$count]['title'] = '<h3><a href="?h=forum&board_id='.$row['id'].'">'.$row['title'].'</a></h3>';
			$data[$count]['description'] = nl2br($row['description']);
			$data[$count]['count'] = $row['count'];
			$count++;
		}
?>		
		<h2><?php echo $page_title; ?> Forum</h2>
		<div class="row th light-bg">
			<div class="col w80">Board</div>
			<div class="col w20">Threads</div>
		</div>
		<div class="row dark-bg">
		<?php foreach($data as $record) { ?>
			<div class="row mv10">
				<div class="col w80">
					<?php echo $record['title'].$record['description']; ?>
				</div>
				<div class="col w20">
					<?php echo $record['count']; ?>
				</div>
			</div>
<?php	}
	?>	</div>
<?php	}

		if($action == "list_threads") {
			// Check if you're allowed to view these threads
			$board_clan_id = mysqli_get_value("clan_id", "board", "id", $board_id);
			if($board_clan_id > 0 && $hackerdata['clan_id'] != $board_clan_id && !$is_staff) return "You are not allowed to view this board.";
			
			// Board entity
			$board_entity = 'public';
			if($board_clan_id == $hackerdata['clan_id']) $board_entity = 'clan';
			
			// Board last visit
			$board_last_visit = $hackerdata[$board_entity.'forumvisit_date'];			
			
			// Board title
			$board_title = '<a href="?h=forum&clan_id='.$board_clan_id.'">'.mysqli_get_value("title", "board", "id", $board_id).'</a>';
			
			// Extra Query
			$extra_sql = '';
			if($hackerdata['clan_council'] == 0) $extra_sql = 'AND thread.council_only = 0';
	
			// Send out the query
			$result = mysqli_query($link, "SELECT thread.*, COUNT(thread_reply.id) as count FROM thread LEFT JOIN thread_reply ON thread_reply.thread_id = thread.id WHERE thread.board_id = $board_id $extra_sql GROUP BY thread.id ORDER BY thread.pinned DESC, thread.creation_date DESC");
			$data = array();
			$count = 0;
			while($row = mysqli_fetch_assoc($result)) {
				// Thread last reply
				$thread_last_reply = mysqli_get_value_from_query("SELECT creation_date FROM thread_reply WHERE thread_id = ".$row['id']." ORDER by creation_date DESC LIMIT 1", "creation_date");
				$new = false;
				if($board_last_visit < $thread_last_reply) $new = ' <span class="red note">[New] </span>';

				// Pinned
				$pinned = '';
				if($row['pinned'] == 1) $pinned = '<span class="red">Pinned: </span>';
				
				// Council only
				$council_only = '';
				if($row['council_only'] == 1) $council_only = '<span class="red note">[Council]</span>';
				
				$data[$count]['title'] = '<h3><a href="?h=forum&thread_id='.$row['id'].'">'.$pinned.$row['title'].$council_only.$new.'</a></h3>';
				$data[$count]['author'] = $row['hacker_id'];
				$data[$count]['creation_date'] = $row['creation_date'];
				$count++;
			}
			
			$update_visit = mysqli_query($link, "UPDATE hacker SET ".$board_entity."forumvisit_date = '$now' WHERE id = ".$hackerdata['id']);
?>
		<h2>> <?php echo $board_title; ?></h2>
		<div class="row th light-bg">
			<div class="col w60">Title</div>
			<div class="col w20">Author</div>
			<div class="col w20">Creation Date</div>
		</div>
		<?php if(mysqli_num_rows($result) > 0) { ?>
		<div class="row dark-bg">
			<?php foreach($data as $record) { ?>
			<div class="row mv10">
				<div class="col w60"><?php echo $record['title']; ?></div>
				<div class="col w20"><?php echo ShowHackerAlias($record['author'], 0); ?></div>
				<div class="col w20"><?php echo Number2Date($record['creation_date']); ?></div>
			</div>
			<?php } ?>
		</div>
		<?php } else PrintMessage("locked", "There are no threads in this board."); ?>
		<form action="index.php" method="POST" class="alt-design">
			<input type="hidden" name="h" value="writethread">
			<input type="hidden" name="board_id" value="<?php echo $board_id; ?>">
			<input type="submit" value="Write a new thread">
		</form>
<?php	}

		if($action == "view_thread") {
			// Check if you are allowed to view the thread.
			$clan_id = mysqli_get_value("clan_id", "board", "id", mysqli_get_value("board_id", "thread", "id", $thread_id));
			if($clan_id > 0 && $hackerdata['clan_id'] != $clan_id && !$is_staff) return "You are not allowed to view this thread.";
			
			// Check if thread exists
			$count = mysqli_get_value_from_query("SELECT count(id) as count FROM thread WHERE id = $thread_id", "count");
			if($count == 0) return "This thread does not exist.";
			
			// Send out the query
			$result = mysqli_query($link, "SELECT * FROM thread WHERE id = ".$thread_id);
			$row = mysqli_fetch_assoc($result);
			
			// Is the thread council only and you aren't council?
			if($row['council_only'] == 1 && $hackerdata['clan_council'] == 0) return "You cannot view this thread.";
			
			// Board title
			$board_title = mysqli_get_value("title", "board", "id", $row['board_id']);
			
			// Page title
			$clan_title = 'Public';
			if($clan_id > 0) $clan_title = mysqli_get_value("alias", "clan", "id", $clan_id);
			$page_title = '> <a href="?h=forum&clan_id='.$clan_id.'">'.$clan_title.' Forums</a> > <a href="?h=forum&board_id='.$row['board_id'].'">'.$board_title.'</a> > '.$row['title'];
			
			// Replies
			$result2 = mysqli_query($link, "SELECT * FROM thread_reply WHERE thread_id = $thread_id ORDER BY creation_date DESC");
			$data = array();
			$count = 0;
			while($row2 = mysqli_fetch_assoc($result2)) {
				$data[$count]['author'] = $row2['hacker_id'];
				$data[$count]['text'] = replaceBBC(nl2br($row2['text']));
				$data[$count]['creation_date'] = $row2['creation_date'];
				if($is_staff || ($hackerdata['clan_id'] == $clan_id && $hackerdata['clan_council'] == 1) || $row2['hacker_id'] == $hackerdata['id']) $data[$count]['delete'] = '[<span class="note"><a href="?h=doforum&action=delete_reply&reply_id='.$row2['id'].'">Delete</a></span>]';
				else $data[$count]['delete'] = '';
				$count++;
			}
?>
		<h2><?php echo $page_title; ?></h2>
		<div class="row light-bg">
			On  <?php echo Number2Date($row['creation_date']); ?> <?php echo ShowHackerAlias($row['hacker_id'], 0); ?>  wrote:
		</div>
		<div class="row dark-bg">
			<?php echo replaceBBC(nl2br($row['message'])); ?>
		</div>
		<?php if(count($data) > 0) { ?>
		<div class="accordion mv10">
    		<input id="replies" type="checkbox" class="accordion-toggle">
        	<label for="replies">Replies</label>
        	<div class="accordion-box">
				<?php foreach($data as $record) { ?>
				<div class="row hr-light mv10">
					<?php echo ShowHackerAlias($record['author'], 0); ?> replied on <?php echo Number2Date($record['creation_date']); ?><br>
					<?php echo $record['delete'].'<br>'; ?>
					<?php echo $record['text']; ?>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
		<?php 
			$can_reply = true;
			if($row['locked'] == 1) { 
				PrintMessage("locked", "This thread is locked and you cannot add replies to it.");
				$can_reply = false;
			}
		?>
		<?php if($row['council_only'] == 1) PrintMessage("warning", "This thread is for councils only."); ?>
		<?php if($can_reply) { ?>
		<div class="accordion mv10">
    		<input id="add_reply" type="checkbox" class="accordion-toggle">
        	<label for="add_reply">Add A Reply</label>
        	<div class="accordion-box">
				<form action="index.php" method="POST" class="mv10">
					<input type="hidden" name="h" value="doforum">
					<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
					<input type="hidden" name="action" value="add_reply">
					<textarea name="reply_text" class="w100i" placeholder="Please enter your reply here...."></textarea>
					<input type="submit" value="Add Reply">
				</form>
			</div>
		</div>
		<?php } ?>
		<?php if($is_staff || ($hackerdata['clan_id'] == $clan_id && $hackerdata['clan_council'] == 1)) { ?>
		<div class="row">
			<form action="index.php" method="POST" class="alt-design">
				<input type="hidden" name="h" value="doforum">
				<input type="hidden" name="action" value="delete_thread">
				<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
				<input type="submit" value="Delete thread">
			</form>
			<form action="index.php" method="POST" class="alt-design">
				<input type="hidden" name="h" value="writethread">
				<input type="hidden" name="action" value="edit">
				<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
				<input type="submit" value="Edit thread">
			</form>
		</div>
		<?php } ?>
		
<?php	}

?>


