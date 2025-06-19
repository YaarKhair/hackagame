<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!empty($_GET['game'])) $game = sql($_GET['game']);
	else return "No game selected.";
	if (!ctype_alnum($game)) die();
	if (!file_exists('./games/'.$game.'.swf')) die();
	echo '
		<table width="100%">
			<thead>
				<tr>
					<th colspan="2">Playing '.$game.'</th>
				</tr>
			</thead>
			<tbody>
			<tr><td>
			<object width="750" height="500">
			<param name="movie" value="./games/'.$game.'.swf">
			<embed src="./games/'.$game.'.swf" width="750" height="500">
			</embed>
			</object>
		</tbody></td></tr></table>';
?>	