<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	$error = "";
	
	$id = 0;
	if (!empty($_REQUEST['id'])) $id = intval($_REQUEST['id']);

	$action = "";
	if (!empty($_REQUEST['action'])) $action = sql($_REQUEST['action']);

	$date = 0;
	if (!empty($_REQUEST['date'])) $date = intval($_REQUEST['date']);


	if ($action == "add") $result = mysqli_query($link, "INSERT INTO doubleep_date (date) VALUES ('$date')");
	else $result = mysqli_query($link, "DELETE FROM doubleep_date (date) WHERE id = $id");
	echo "Working on it...";
?>
	<script>
	setTimeout(function() {
	  window.location.href = '?h=moddoubleep';
	}, 1000);
	</script>