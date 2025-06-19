<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) return "Invalid page.";
	
	$id = 0;
	if(!empty($_REQUEST['id'])) $id = intval($_REQUEST['id']);
	
	$page = 1;
	if(!empty($_REQUEST['page'])) $page = intval($_REQUEST['page']);
	$extra_query = "LIMIT ".(($page - 1) * $dyk_display).", ".$dyk_display;
	
	// Total count of records
	$result = mysqli_query($link, "SELECT COUNT(*) AS total FROM dyk");
	$row = mysqli_fetch_assoc($result);
	$total = $row['total'];
	
	// Display all records
	$result = mysqli_query($link, "SELECT * FROM dyk ORDER BY id DESC $extra_query");
	echo "<div class='col50left'><table>";
	echo "<caption>Did you know tooltips</caption>";
	echo "<tr>";
	echo "<th>Message</th><th>Action</th></tr>";
	while($row = mysqli_fetch_assoc($result)) {
		echo "<tr>";
		echo "<td>".$row['tooltip']."</td>";
		echo "<td>[<a href='index.php?h=moddyk&id={$row['id']}'>Edit</a>]<br>[<a href='index.php?h=domoddyk&action=delete&id={$row['id']}'>Delete</a>]</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "Page: ";
	for($i = 1; $i <= ceil($total / $dyk_display); $i++) echo "<a href='?h=moddyk&page=$i'>$i</a>&nbsp;&nbsp;";
	echo "</div>";
	
	// Fill an array with some values
	$values = array();
	$values['tooltip'] = '';
	$values['id'] = 0;
	$values['action'] = 'Add';
	if($id > 0) {
		$result = mysqli_query($link, "SELECT * FROM dyk WHERE id = $id");
		while($row = mysqli_fetch_assoc($result)) {
			$values['tooltip'] = $row['tooltip'];
			$values['id'] = $row['id'];
			$values['action'] = 'Edit';
		}
	}

?>
<!--- Add form --->
<div class="col50right">
	<table>
	<caption><?php echo $values['action']; ?> DYK</caption>
	<form action="index.php" method="POST">
	<input type="hidden" name="h" value="domoddyk">
	<input type="hidden" name="action" value="<?php echo $values['action']; ?>">
	<input type="hidden" name="id" value="<?php echo $values['id']; ?>">	
	<tr>
		<td>Tooltip</td>
		<td><textarea name="tooltip" rows="5" cols="30"><?php echo $values['tooltip']; ?></textarea>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="<?php echo $values['action']; ?>"></td>
	</tr>
	</form>
	</table>
</div>