<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	//http://elegans.imbb.forth.gr/games/
	echo '
		<table width="40%">
			<thead>
				<tr>
					<th>Game List</th>
				</tr>
			</thead>
			<tbody>
			<tr><td>';
			
	if ($dir = @opendir("./games")) {
		while (($file = readdir($dir)) !== false) {
			if ($file != "." && $file != ".." && $file != "index.php") {
				$game = explode(".", $file);
				echo '<a href="index.php?h=playgame&game='.$game[0].'">'.$game[0].'</a><br>';
			}
		}  
		closedir($dir);
	}
	echo '</td></tr></tbody></table>';
?>	