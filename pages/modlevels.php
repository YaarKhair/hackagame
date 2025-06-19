<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
		$level = 0;
		$min_ep = $level_prime;
		echo '<table width="20%">
				<caption>Level Sheet</caption>
				<thead>
					<tr>
						<th>Min EP</th>
						<th>Max EP</th>
						<th>Level</th>
					</tr>
				</thead>
				<tbody>';
		
		while ($level < $maxlevel) {
			$max_ep = $min_ep * $level_up;
			$level ++;
			echo "<tr><td>".round($min_ep - $level_prime)."</td><td>".round($max_ep - $level_prime)."</td><td>".$level."</td></tr>";
			$min_ep = $max_ep;
		}
		echo '</tbody></table>';
?>		
