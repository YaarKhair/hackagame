<?php
	include "$gamepath/modules/libchart/classes/libchart.php";

	// number of hackers
	$result = mysqli_query($link, "SELECT id FROM hacker WHERE npc = 0");
	$num_hackers = mysqli_num_rows($result);
	
	// number of clans
	$result = mysqli_query($link, "SELECT id FROM clan");
	$num_clans = mysqli_num_rows($result);
	
	// number of owned servers
	/*$result = mysqli_query($link, "SELECT id FROM server WHERE hacker_id <> 0");
	$num_servers = mysqli_num_rows($result);
	$perc_servers = round(($num_servers / 2500) * 100);*/
	
	$stats = '
	<table width="40%">
		<thead>
			<tr><th colspan="2">General Stats</th></tr>
		</thead>
		<tbody>
			<tr><th>Registered Hackers:</th><td>'.number_format($num_hackers).'</td></tr>
			<tr><th>Formed Clans:</th><td>'.number_format($num_clans).'</td></tr>
		</tbody>	
	</table>
	<br><br>';
	sleep (5);
	
	// hackers with most hackpoints
	$result = mysqli_query($link, "SELECT id, alias, hackpoints FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY hackpoints DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Best PvP Hacker</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['hackpoints'].")", $row['hackpoints']));		
	}
	$image = "stats_hackerpvp.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Best PvP Hackers");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
	
	
	// dominating clans
	$result = mysqli_query($link, "SELECT clan.id, clan.color, clan.alias, Count(DISTINCT hacker.id) AS members, Count(server.id) AS servers FROM ((clan LEFT JOIN hacker ON clan.id = hacker.clan_id) LEFT JOIN server ON hacker.id = server.hacker_id) WHERE clan.id <> $staff_clanid AND clan.active = 1 GROUP BY clan.id, clan.alias ORDER BY servers DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Dominating Clans</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['servers'].")", $row['servers']));	
		
	}
	$image = "stats_domination.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Dominating Clans");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';
	sleep (5);
	
/*	// clans with most ep
	$result = mysqli_query($link, "SELECT clan.id, clan.color, clan.alias, Count(DISTINCT hacker.id) AS members, Sum(hacker.ep) AS ep FROM clan LEFT JOIN hacker ON clan.id = hacker.clan_id WHERE clan.active = 1 AND hacker.clan_id <> $staff_clanid GROUP BY clan.id, clan.alias ORDER BY ep DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				 <th>Most Experienced Clans</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['ep'].")", $row['ep']));		
	}
	$image = "stats_clanep.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Most Experienced Clans");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';
	sleep (5);
*/

/*
	// hackers with most ep
	$result = mysqli_query($link, "SELECT id, alias, ep FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY ep DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Most Experienced Hackers</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['ep'].")", $row['ep']));		
	}
	$image = "stats_hackerep.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Most Experienced Hackers");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
*/

/*	// hackers with most skill
	$result = mysqli_query($link, "SELECT id, alias, skill FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY skill DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Most Skilled Hackers</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['skill'].")", $row['skill']));		
	}
	$image = "stats_hackerskill.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Most Skilled Hackers");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
*/	
	
	// hackers most pc's hacked
	$result = mysqli_query($link, "SELECT id, alias, pchack_win FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY pchack_win DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Most PCs Hacked</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['pchack_win'].")", $row['pchack_win']));		
	}
	$image = "stats_hackerpc.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Most PCs Compromised");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
	
	// hackers pc's hack efficiency
	$result = mysqli_query($link, "SELECT id, alias, pchack_win, pchack_fail, (pchack_win / (pchack_win + pchack_fail)) * 100 AS efficiency FROM hacker WHERE active = 1 AND pchack_win + pchack_fail > 10 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 and hacker.npc = 0 ORDER BY efficiency DESC limit 10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th colspan="2">Most Efficient PC Hacker</th>
				<th>Efficiency</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$stats .= '<tr><td>'.$rowcount.'</td><td><a href="index.php?h=profile&id='.$row['id'].'">'.$row['alias'].'</a></td><td>'.round(intval($row['efficiency'])).'% ('.$row['pchack_win'].'/'.$row['pchack_fail'].')</td></tr>';
	}
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
	
	// hackers most servers hacked
	$result = mysqli_query($link, "SELECT id, alias, serverhack_win FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY serverhack_win DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Most Servers Hacked</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['serverhack_win'].")", $row['serverhack_win']));		
	}
	$image = "stats_hackerserver.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Most Servers Compromised");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
	
	// hackers server's hack efficiency
	$result = mysqli_query($link, "SELECT id, alias, serverhack_win, serverhack_fail, (serverhack_win / (serverhack_win + serverhack_fail)) * 100 AS efficiency FROM hacker WHERE active = 1 AND serverhack_win + serverhack_fail > 10 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY efficiency DESC limit 10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th colspan="2">Most Efficient Server Hacker</th>
				<th>Efficiency</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$stats .= '<tr><td>'.$rowcount.'</td><td><a href="index.php?h=profile&id='.$row['id'].'">'.$row['alias'].'</a></td><td>'.round(intval($row['efficiency'])).'% ('.$row['serverhack_win'].'/'.$row['serverhack_fail'].')</td></tr>';
	}
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
	
	// hackers most servers infected
	$result = mysqli_query($link, "SELECT id, alias, serversinfected FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY serversinfected DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Most Servers Infected</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['serversinfected'].")", $row['serversinfected']));		
	}
	$image = "stats_hackerinfected.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Most Servers Infected");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
		
	// hackers most servers killed
	$result = mysqli_query($link, "SELECT id, alias, serverskilled FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY serverskilled DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Most Servers Killed</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['serverskilled'].")", $row['serverskilled']));		
	}
	$image = "stats_hackerkilled.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Most Servers Killed");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
		
	// hackers most bounties
	$result = mysqli_query($link, "SELECT id, alias, bountiescollected FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 ORDER BY bountiescollected DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th>Most Bounties Collected</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	$chart = new PieChart(500, 350);
	$dataSet = new XYDataSet();
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$dataSet->addPoint(new Point($row['alias']."(".$row['bountiescollected'].")", $row['bountiescollected']));		
	}
	$image = "stats_hackerbounties.png";
	$file_name = "$gamepath/images/".$image;
	$url_name = "/images/".$image;
	$chart->setDataSet($dataSet);
	$chart->setTitle("Most Bounties Collected");
	$chart->render($file_name);
	$stats .= '<tr><td bgcolor="#FFFFFF"><div align="center"><img src="'.$url_name.'"></div></td></tr>';
	$stats .= '</tbody>
	</table>
	<br><br>';

	sleep (5);
		
/*	// best reputation
	$result = mysqli_query($link, "SELECT alias, id, sum(rep_points_plus - rep_points_minus) as Reputation, rep_points_plus, rep_points_minus FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 GROUP BY id ORDER BY Reputation DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th colspan="2">Most Valued Hacker</th>
				<th>Points</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$stats .= '<tr><td>'.$rowcount.'</td><td><a href="index.php?h=profile&id='.$row['id'].'">'.$row['alias'].'</a></td><td>'.number_format($row['Reputation']).' (+'.number_format($row['rep_points_plus']).'/-'.number_format($row['rep_points_minus']).')</td></tr>';
	}
	$stats .= '</tbody>
	</table>
	<br><br>';
	
	sleep (5);

	// worst reputation
	$result = mysqli_query($link, "SELECT alias, id, sum(rep_points_plus - rep_points_minus) as Reputation, rep_points_plus, rep_points_minus FROM hacker WHERE active = 1 AND hacker.clan_id <> $staff_clanid AND banned_date = 0 AND hacker.npc = 0 GROUP BY id ORDER BY Reputation ASC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th colspan="2">Least Valued Hacker</th>
				<th>Points</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$stats .= '<tr><td>'.$rowcount.'</td><td><a href="index.php?h=profile&id='.$row['id'].'">'.$row['alias'].'</a></td><td>'.number_format($row['Reputation']).' (+'.number_format($row['rep_points_plus']).'/-'.number_format($row['rep_points_minus']).')</td></tr>';
	}
	$stats .= '</tbody>
	</table>
	<br><br>';
	
	sleep (5);

	// best reputation
	$result = mysqli_query($link, "SELECT alias, id, sum(rep_points_plus - rep_points_minus) as Reputation, rep_points_plus, rep_points_minus FROM clan WHERE active = 1 AND id <> $staff_clanid GROUP BY id ORDER BY Reputation DESC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th colspan="2">Most Valued Clans</th>
				<th>Points</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$stats .= '<tr><td>'.$rowcount.'</td><td><a href="index.php?h=claninfo&id='.$row['id'].'">'.$row['alias'].'</a></td><td>'.number_format($row['Reputation']).' (+'.number_format($row['rep_points_plus']).'/-'.number_format($row['rep_points_minus']).')</td></tr>';
	}
	$stats .= '</tbody>
	</table>
	<br><br>';
	
	sleep (5);

	// worst reputation
	$result = mysqli_query($link, "SELECT alias, id, sum(rep_points_plus - rep_points_minus) as Reputation, rep_points_plus, rep_points_minus FROM clan WHERE active = 1 AND id <> $staff_clanid GROUP BY id ORDER BY Reputation ASC LIMIT 0,10");
	$stats .= '
	<table width="40%">
		<thead>
			<tr>
				<th colspan="2">Least Valued Clan</th>
				<th>Points</th>
			</tr>
		</thead>
		<tbody>';
		
	$rowcount = 0;	
	while ($row = mysqli_fetch_assoc($result)) {
		$rowcount ++;
		$stats .= '<tr><td>'.$rowcount.'</td><td><a href="index.php?h=claninfo&id='.$row['id'].'">'.$row['alias'].'</a></td><td>'.number_format($row['Reputation']).' (+'.number_format($row['rep_points_plus']).'/-'.number_format($row['rep_points_minus']).')</td></tr>';
	}
	$stats .= '</tbody>
	</table>
	<br><br>';*/
	
	sleep (5);

	// total hacker cash
	$result = mysqli_query($link, "SELECT Sum(bankaccount) AS BankTotal FROM hacker WHERE active = 1 AND npc = 0 AND clan_id <> $staff_clanid");
	$row = mysqli_fetch_assoc($result);
	$total_hacker_cash = $row['BankTotal'];
	
	
	// total clan cash
	$result = mysqli_query($link, "SELECT Sum(bankaccount) AS BankTotal FROM clan WHERE active = 1 AND id != 1"); // Not GA
	$row = mysqli_fetch_assoc($result);
	$total_clan_cash = $row['BankTotal'];
	
	// total swiss cash
	$result = mysqli_query($link, "SELECT Sum(amount) AS BankTotal FROM swissbank");
	$row = mysqli_fetch_assoc($result);
	$total_swiss_cash = $row['BankTotal'];
	if ($total_swiss_cash == NULL) { $total_swiss_cash = 0; }
	
	// total lottery pot
	$result = mysqli_query($link, "SELECT id FROM lottery");
	$total_lottery_cash = mysqli_num_rows($result) * $lottery_price;
	if ($total_lottery_cash == NULL) { $total_lottery_cash = 0; }
	
	// display info
	$stats .= '
	<table width="40%">
		<thead>
			<tr><th colspan="2">Money Stats</th></tr>
		</thead>
		<tbody>
			
			<tr><th>Total Hacker Cash:</th><td><div align="right">'.$currency.number_format($total_hacker_cash).'</div></td></tr>
			<tr><th>Total Clan Cash:</th><td><div align="right">'.$currency.number_format($total_clan_cash).'</div></td></tr>
			<tr><th>In Swiss Bank:</th><td><div align="right">'.$currency.number_format($total_swiss_cash).'</div></td></tr>
			<tr><th>Lottery Pot:</th><td><div align="right">'.$currency.number_format($total_lottery_cash).'</div></td></tr>
			<tr><th colspan="2"><hr></th></tr>
			<tr><th>Total In-Game Cash:</th><td><div align="right">'.$currency.number_format($total_hacker_cash+$total_clan_cash+$total_swiss_cash+$total_lottery_cash).'</div></td></tr>
		</tbody>	
	</table><br><br>';
	
	sleep (5);

	// economy
	$result = mysqli_query($link, "SELECT * FROM economy ORDER BY id DESC LIMIT 2");
	
	// today
	$row = mysqli_fetch_assoc($result);
	
	// save the news into today
	$result2 = mysqli_query($link, "UPDATE economy SET hacker_cash = $total_hacker_cash, clan_cash = $total_clan_cash, swiss_cash = $total_swiss_cash, lottery_cash = $total_lottery_cash WHERE id = {$row['id']}");
	
	$spam1 = $row['spam_economy'];
	$phishing1 = $row['phishing_economy'];
	$porn1 = $row['porn_economy'];
	$filesharing1 = $row['filesharing_economy'];
	
	// yesterday
	$row = mysqli_fetch_assoc($result);
	$spam2 = $row['spam_economy'];
	$phishing2 = $row['phishing_economy'];
	$porn2 = $row['porn_economy'];
	$filesharing2 = $row['filesharing_economy'];
	
	// difference
	$spam3 = $spam1 - $spam2; if ($spam3 > 0) $spam3 = '+'.$spam3;
	$phishing3 = $phishing1 - $phishing2; if ($phishing3 > 0) $phishing3 = '+'.$phishing3;
	$porn3 = $porn1 - $porn2; if ($porn3 > 0) $porn3 = '+'.$porn3;
	$filesharing3 = $filesharing1 - $filesharing2; if ($filesharing3 > 0) $filesharing3 = '+'.$filesharing3;
				
	$stats .= '
	<table width="40%">
		<thead>
			<tr><th colspan="2">Economy</th></tr>
		</thead>
		<tbody>
			
			<tr><th>Spam Economy:</th><td><div align="right">'.$spam1.' ('.$spam3.')</div></td></tr>
			<tr><th>Phishing Economy:</th><td><div align="right">'.$phishing1.' ('.$phishing3.')</div></td></tr>
			<tr><th>Porn Economy:</th><td><div align="right">'.$porn1.' ('.$porn3.')</div></td></tr>
			<tr><th>File Economy:</th><td><div align="right">'.$filesharing1.' ('.$filesharing3.')</div></td></tr>
		</tbody>	
	</table><br><br>';
			
	$stats .= '<hr><div align="right">Generated@'.Number2Date($now).'<br>';
	$stats .= 'Next@'.Number2Date(date($date_format, strtotime("+24 hour"))).'</div>';
	
	sleep (5);
	
	// write stats.php
	$myFile = "$gamepath/pages/stats.php";
	$fh = fopen($myFile, 'w') or die("fail");
	fwrite($fh, $stats);
	fclose($fh);
?>
