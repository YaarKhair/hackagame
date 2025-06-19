<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) return "Invalid page.";
	
	$id = 0;
	if(!empty($_REQUEST['id'])) $id = intval($_REQUEST['id']);
	
	// Display all records
	$result = mysqli_query($link, "SELECT * FROM tutorial ORDER BY LEVEL ASC");
	echo "<table>";
	echo "<caption>Tutorial messages</caption>";
	echo "<tr><th>Level</th><th>Title</th><th>Action</th></tr>";
	while($row = mysqli_fetch_assoc($result)) {
		echo "<tr>";
		echo "<td>".$row['level']."</td>";
		echo "<td>".$row['title']."</td>";
		echo "<td>[<a href='index.php?h=modtutorial&id={$row['id']}'>View</a>] [<a href='index.php?h=domodtutorial&action=delete&id={$row['id']}'>Delete</a>]</td>";
		echo "</tr>";
	}
	echo "</table><br><br>";
	
	// Fill an array with some values
	$values = array();
	$values['level'] = '';
	$values['title'] = '';
	$values['message'] = '';
	$values['id'] = 0;
	$values['action'] = 'Add';
	if($id > 0) {
		$result = mysqli_query($link, "SELECT level, title, message, id FROM tutorial WHERE id = $id");
		while($row = mysqli_fetch_assoc($result)) {
			$values['level'] = $row['level'];
			$values['title'] = $row['title'];
			$values['message'] = $row['message'];
			$values['id'] = $row['id'];
			$values['action'] = 'edit';
		}
	}
	
?>
<!--- Add form --->
	<table>
	<caption><?php echo $values['action']; ?> tutorial</caption>
	<form action="index.php" method="POST">
	<input type="hidden" name="h" value="domodtutorial">
	<input type="hidden" name="action" value="<?php echo $values['action']; ?>">
	<input type="hidden" name="id" value="<?php echo $values['id']; ?>">	
	<tr>
		<td>Level</td>
		<td><input type="text" name="level" value="<?php echo $values['level']; ?>"></td>
	</tr>
	<tr>
		<td>Title</td>
		<td><input type="text" name="title" value="<?php echo $values['title']; ?>"></td>
	</tr>
	<tr>
		<td>Message</td>
		<td><textarea name="message" style="max-width: 500px !important; width: 500px !important; height: 270px !important"><?php echo br2nl(stripslashes(strip_tags($values['message'], "<br><br>" ))); ?></textarea></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="Save"></td>
	</tr>
	</form>
	</table>
