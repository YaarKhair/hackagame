<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$_SESSION['loaded'] = 0; // anti refresh
	$error = "";
	$searchfor = "*"; // if you send nothing, you will find nothing (the really lazy way)
	if (!empty($_POST['searchfor'])) $searchfor = sql($_POST['searchfor']);
	
	echo '
		<table width="100%">
			<caption>FTP Search Results for "'.$searchfor.'" (first 20 hits, ordered by price)</caption>
			<thead>
				<tr>
					<th>File</th>
					<th>Size</th>
					<th>Server</th>
					<th>Uploader</th>
					<th>Uploaded</th>
					<th>Price</th>
					<th>Buy</th>
				</tr>
			</thead>
			<tbody>';
		// read files on this FTP server	
		$result = mysqli_query($link, "select inventory.*, server.ftp_title, server.ip, server.hacker_id as owner_id from inventory left join file on inventory.file_id = file.id left join product on inventory.product_id = product.id left join server on inventory.server_id = server.id WHERE server.ftp_public = 1 AND inventory.server_id > 0 AND (file.title like '%$searchfor%' OR product.title like '%$searchfor%') ORDER BY price ASC LIMIT 20") or die(mysqli_error($link));
		if (mysqli_num_rows($result) == 0) { 
			echo '<tr><td colspan="7">No files found</td></tr>';
		}
		else {
			$color = 0;
			while ($row = mysqli_fetch_assoc ($result)) {
				$checksum = sha1($row['ip']);
				$color ++; if ($color ==2) $color = 0;						
				if ($color == 1) $bgcolor = "#000000";	else $bgcolor = "#181818";
				$dellink = '';
				if ($row['owner_id'] == $hackerdata['id'] || $row['hacker_id'] == $hackerdata['id']) $dellink = '[<a href="?h=dodeletefile&id='.$row['id'].'"><font color="red">X</font></a>] ';
				$infolink = '';
				if ($row['product_id'] != 0) $infolink = '&nbsp;[<a href="?h=productinfo&product_id='.$row['product_id'].'">Info</a>] ';
				if ($row['price'] > 0) $downloadlink = '<a href="index.php?h=download&inventory_id='.$row['id'].'&server_id='.$row['server_id'].'&checksum='.$checksum.'">Download</a>';
				else $downloadlink = '<a href="index.php?h=download&inventory_id='.$row['id'].'&server_id='.$row['server_id'].'&checksum='.$checksum.'">View</a> ('.$row['downloads'].' views)';
				echo '<tr bgcolor="'.$bgcolor.'">
						<td>'.$dellink.FileInfo($row['id'], "title").$infolink.'</td>
						<td>'.DisplaySize(FileInfo($row['id'], "size")).'</td>
						<td><a href="index.php?h=ftp&server_ip='.$row['ip'].'">'.$row['ftp_title'].'</a></td>
						<td>'.ShowHackerAlias($row['hacker_id'],1).'</td>
						<td>'.Number2Date($row['datechanged']).'</td>
						<td>'.$currency.number_format($row['price']).'</td>
						<td>'.$downloadlink.'</td>
						</tr>';
			}
		}
		echo '</tbody></table>';
?>