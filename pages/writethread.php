<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include("modules/permissions.php");

	// Initialis
	$action = "new";
	if(!empty($_POST['action'])) $action = 'edit';
	
	$board_title = '';
	$thread_title = '';
	$thread_body = '';
	$council_only = "";
	$locked = "";
	$pinned = "";
	$action_form = "add_thread";
	
	if($action == "new") {
		// Get the board ID
		$board_id = 0;
		if(!empty($_POST['board_id']) && $_POST['board_id'] > 0) $board_id = intval($_POST['board_id']);

		// Check if the board exists
		$count = mysqli_get_value_from_query("SELECT COUNT(id) as count FROM board WHERE id = $board_id", "count");
		if($count == 0) return "Invalid board.";

		// Check if you are allowed to write for this board
		$clan_id = mysqli_get_value("clan_id", "board", "id", $board_id);
		if($clan_id > 0 && $clan_id != $hackerdata['clan_id']) return "You are not allowed to write for this board.";

		// Board name
		$board_title = mysqli_get_value("title", "board", "id", $board_id);
		
		// Add
		$field = 'board_id';
		$value = $board_id;
	}
	
	if($action == "edit") {
		// Get the thread ID
		$thread_id = 0;
		if(!empty($_POST['thread_id']) && $_POST['thread_id'] > 0) $thread_id = intval($_POST['thread_id']);
		
		// Check if you thread exists
		$count = mysqli_get_value_from_query("SELECT count(id) as count FROM thread WHERE id = $thread_id", "count");
		if($count == 0) return "This thread does not exist.";
		
		// Get the clan id
		$clan_id = mysqli_get_value("clan_id", "board", "id", mysqli_get_value("board_id", "thread", "id", $thread_id));
		
		// Check if you have editing permissions
		$can_edit = false;
		if($is_staff) $can_edit = true;
		if($clan_id == $hackerdata['clan_id'] && $hackerdata['clan_council'] == 1) $can_edit = true;
		if($clan_id == 0) $can_edit = true;
		if(!$can_edit) return "You cannot modify this thread.";
		
		// Query the shizzle, my nizzle
		$result = mysqli_query($link, "SELECT * FROM thread WHERE id = $thread_id");
		$row = mysqli_fetch_assoc($result);
		
		// Values
		$board_title = mysqli_get_value("title", "board", "id", $row['board_id']);
		$thread_title = $row['title'];
		$thread_body = br2nl($row['message']);
		if($row['council_only'] == 1) $council_only = 'checked = "true"';
		if($row['locked'] == 1) $locked = 'checked = "true"';
		if($row['pinned'] == 1) $pinned = 'checked = "true"';
		$action_form = "edit_thread";
		$field = "thread_id";
		$value = $thread_id;
	}
	
?>
	<h2>Write a new thread [<?php echo $board_title; ?>]</h2>
	<form action="index.php" method="POST">
		<input type="hidden" name="h" value="doforum">
		<input type="hidden" name="action" value="<?php echo $action_form; ?>">
		<input type="hidden" name="<?php echo $field; ?>" value="<?php echo $value; ?>">
		<div class="row light-bg">
			<div class="row">
				<div class="col w20">
					Title:
				</div>
				<div class="col w80">
					<input type="text" name="thread_title" placeholder="Thread Title.." value="<?php echo $thread_title; ?>">
				</div>
			</div>
			<div class="row">
				<div class="col w20">
					Body:
				</div>
				<div class="col w80">
					<textarea name="thread_body" class="w100i h350" placeholder="Thread Body..."><?php echo $thread_body; ?></textarea>
				</div>		
			</div>
			<?php if(($hackerdata['clan_council'] == 1 && $hackerdata['clan_id'] == $clan_id) || $is_staff) { ?>
			<div class="row mv10">
				<div class="col w20">Council Only</div>
				<div class="col w80"><input type="checkbox" name="council_only" id="council_only" <?php echo $council_only; ?>><label for="council_only"></label></div>
			</div>
			<div class="row mv10">
				<div class="col w20">Locked</div>
				<div class="col w80"><input type="checkbox" name="locked" id="locked" <?php echo $locked; ?>><label for="locked"></label></div>
			</div>
			<div class="row mv10">
				<div class="col w20">Pinned</div>
				<div class="col w80"><input type="checkbox" name="pinned" id="pinned" <?php echo $pinned; ?>><label for="pinned"></label></div>
			</div>
			<?php } ?>
			<div class="row">
				<div class="col w100">
					<input type="submit" value="Submit thread">
				</div>
			</div>
		</div>
	</form>