<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php	$_SESSION['loaded'] = 0; // anti refresh ?>
<?php
if (!HasInstalled($hackerdata['id'], "HDD")) PrintMessage ("Error", "Harddrive S.M.A.R.T. failure. Defect.", "40%"); 
else {
	echo '
		<h1>Downloads</h1>
			<div class="row th">
				<div class="col w50">Title</div>
				<div class="col w25">Size</div>
				<div class="col w25">Modified</div>
			</div>
		<div class="light-bg">';
	$query = "SELECT inventory.id, inventory.product_id, inventory.file_id, inventory.datechanged, product.can_install FROM inventory LEFT JOIN product on inventory.product_id = product.id LEFT JOIN filetransfer ON inventory.id = filetransfer.inventory_id WHERE inventory.server_id = 0 AND inventory.hacker_id = {$hackerdata['id']} AND ISNULL(filetransfer.id) ORDER BY inventory.id DESC";
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) echo '<tr><td colspan="3">Empty HDD</td></tr>';
	
	$total_size = 0;
	$_SESSION['loaded'] = 0; // anti refresh
	while ($row = mysqli_fetch_assoc($result)) {
		$filelink = '';
		if ($row['file_id'] > 0) 
			$filelink = '&nbsp;[<a href="?h=notepad&inventory_id='.$row['id'].'">View</a>]';
		if ($row['product_id'] > 0) 
			if ($row['can_install'] == 1) 
				$filelink = '&nbsp;[<a href="?h=douseitem&inventory_id='.$row['id'].'">Install</a>]';
		
		$infolink = '';
		if ($row['product_id'] != 0) $infolink = '&nbsp;[<a href="?h=productinfo&product_id='.$row['product_id'].'">Info</a>] ';
		echo '
			<div class="row hr-light">
				<div class="col w50"><a href="?h=dodeletefile&id='.$row['id'].'" class="bg-red"><span class="red">X</span></a>&nbsp;'.FileInfo($row['id'], "title").$infolink.$filelink.'</div>
				<div class="col w25">'.DisplaySize(FileInfo($row['id'], "size")).'</div>	
				<div class="col w25">'.Number2Date($row['datechanged']).'</div>	
			</div>';
	}
	echo '</div>';
	
	// if there is something on your hdd you can send it.
	if (mysqli_num_rows($result) > 0) {
	echo '
	<br>
	<br>
		<h1>SendLink</h1>
			<div class="row th">
				<div class="col w50">Title</div>
				<div class="col w50">Hacker</div>
			</div>
			<div class="light-bg">
			<div class="row hr-light">
				<div class="col w50">
					<form method="POST" action="index.php"><input type="hidden" name="h" value="dosendlink">
					<select name="inventory_id">';
				$query = "SELECT inventory.id FROM inventory LEFT JOIN filetransfer ON inventory.id = filetransfer.inventory_id WHERE inventory.server_id = 0 AND inventory.hacker_id = {$hackerdata['id']} AND ISNULL(filetransfer.id) ORDER BY inventory.id DESC";
				$result = mysqli_query($link, $query);
				while ($row = mysqli_fetch_assoc($result)) echo '<option value="'.$row['id'].'">'.FileInfo($row['id'], "title");
	echo '			
				</select>
			</div>
			<div class="col w50">';
				if (mysqli_num_rows($result) > 0) {
					echo '
						<input type="text" value="" size="15" maxlength="15" name="username">
						<input type="submit" value="send link">';
				}		
	echo '			
				</form>
			</div>	
		</div>
		</div>';
	}
}
?>