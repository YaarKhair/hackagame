<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
	// total in game money for the past 20 days
	$number = 20;
	$result = mysqli_query($link, "SELECT * FROM (SELECT id, day, hacker_cash, clan_cash, swiss_cash, lottery_cash FROM economy WHERE hacker_cash <> 0 ORDER BY id DESC LIMIT $number) as tbl ORDER BY tbl.id ASC");
	$prev = Array();
	$prev['hacker_cash'] = 0;
	$prev['clan_cash'] = 0;
	$prev['swiss_cash'] = 0;
	$prev['lottery_cash'] = 0;
	$prev_total = 0; // outside of the array for the loop
	
	echo '
		<table>
			<caption>'.$number.' Days - Total Money History</caption>
			<thead>
				<tr>
					<th>Day of Month</th>
					<th>Hacker Cash</th>
					<th>Clan Cash</th>
					<th>Swiss Cash</th>
					<th>Lottery Cash</th>
					<th>Total Cash</th>
				</tr>
			</thead>
			<tbody>';
	while ($row = mysqli_fetch_assoc($result)) {
		echo "<tr>";
		
		echo "<td>{$row['day']}</td>";
		$total = 0;
		foreach( $prev as $key => $value) {
			echo "<td>";
			echo number_format($row[$key]);
			if ($row[$key] > $prev[$key]) $class = "bad";
			else $class = "good";
			echo "&nbsp;(<span class=\"$class\">".number_format($row[$key] - $prev[$key])."</span>)";
			$prev[$key] = $row[$key];
			$total += $row[$key];
			echo "</td>";
		}
		// calculate the total
		echo "<td>";
		echo number_format($total);
		if ($total > $prev_total) $class = "bad";
		else $class = "good";
		echo "&nbsp;(<span class=\"$class\">".number_format($total - $prev_total)."</span>)";
		$prev_total = $total;
		echo "</td>";
		
		echo "</tr>";
	}		
	echo "</tbody></table>";
	echo "<br><br>";
	
	$result = mysqli_query($link, "select sum(gamble_won) as won, sum(gamble_lost) as lost from hacker");
	$row = mysqli_fetch_assoc($result);
	
	if ($row['lost'] >= $row['won']) $class = "good";
	else $class = "bad";
	
	echo '<table>
			<caption>Casino</caption>
			<tbody>
			<tr><th colspan="2">General</th></tr>
			<tr><td colspan="2">
			Players won '.number_format($row['won']).' in the casino.<br>
			Players lost '.number_format($row['lost']).' in the casino.<br>
			Casino profit: <span class="'.$class.'">'.number_format($row['lost'] - $row['won']).'</span></td></tr>
			<tr><th colspan="2">Top 10 big rollers</th></tr>';
			
			$result = mysqli_query($link, "SELECT id, clan_id, gamble_lost, gamble_won FROM hacker ORDER BY gamble_won - gamble_lost DESC LIMIT 10");
			while ($row = mysqli_fetch_assoc($result))
				echo '<tr><td>'.ShowHackerAlias($row['id'], $row['clan_id']).'</td><td>'.$currency.number_format($row['gamble_won']-$row['gamble_lost']).' <em>(won: '.$currency.number_format($row['gamble_won']).' - lost: '.$currency.number_format($row['gamble_lost']).')</em><br>';
			
			echo '</td></tr>
			</table><br><br>';

	$number = 50;
	$result = mysqli_query($link, "SELECT id, last_click, bankaccount, clan_id FROM hacker WHERE clan_id <> $staff_clanid AND active = 1 AND bankaccount > 0 ORDER BY bankaccount DESC LIMIT $number");
	
	echo '
		<div class="col50left">
		<table>
			<caption>'.$number.' Richest Hackers</caption>
			<tbody>';
	
	while ($row = mysqli_fetch_assoc($result))
		echo '<tr><td>'.ShowHackerAlias($row['id'], $row['clan_id']).'</td><td>'.$currency.number_format($row['bankaccount']).'</td><td>'.Number2Date($row['last_click']).'</tr>';
	
	echo '
		</tbody></table>
		</div>';
		
	$result = mysqli_query($link, "SELECT id, bankaccount FROM clan WHERE id <> $staff_clanid AND active = 1 AND bankaccount > 0 ORDER BY bankaccount DESC LIMIT $number");
	
	echo '
		<div class="col50right">
		<table>
			<caption>'.$number.' Richest Clans</caption>
			<tbody>';
	
	while ($row = mysqli_fetch_assoc($result))
		echo '<tr><td>'.ShowClanAlias($row['id']).'</td><td>'.$currency.number_format($row['bankaccount']).'</td></tr>';
	
	echo '
		</tbody></table>
		</div>';
?>