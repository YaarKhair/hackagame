<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$error = "";
	
	$note_id = 0;
	if (!empty($_POST['note_id'])) $note_id = sql(intval($_POST['note_id']));
	elseif (!empty($_GET['note_id'])) $note_id = sql(intval($_GET['note_id']));

	$hacker_id =0;
	if (!empty($_POST['hacker_id'])) $hacker_id = intval($_POST['hacker_id']);
	elseif (!empty($_GET['hacker_id'])) $hacker_id = intval($_GET['hacker_id']);
	
	$action = "";
	if (!empty($_POST['action'])) $action = sql($_POST['action']);
	elseif (!empty($_GET['action'])) $action = sql($_GET['action']);

	$message = "";
	if (!empty($_POST['message'])) {
		$message = $_POST['message'];
		$message = preg_replace('#\r?\n#', '[br]', $message);
		$message = sql($message);
		$message = str_replace("[br]", "<br>", $message);
	}
	
	if ($error == "") {
		if ($action == "post_note") {
			$result = mysqli_query($link, "INSERT INTO modnote (creator_id, hacker_id, date, message) VALUES ({$hackerdata['id']}, $hacker_id, '$now', '$message')");
		}
		if ($action == "delete_note") {
			$result = mysqli_query($link, "DELETE FROM modnote WHERE id = $note_id");
		}
		echo '<script type="text/javascript">location.href = "?h=profile&id='.$hacker_id.'"</script>';
	}
	else {
		PrintMessage ("Error", $error, "40%");
	}	
?>