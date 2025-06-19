<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$query = "select hacker.country, country.name, count(hacker.country) as numHackers from hacker left join country on hacker.country = country.code WHERE banned_date = 0 AND npc = 0 group by hacker.country order by count(hacker.country) desc";
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) return "No hackers found.";
	$counter = 0;
	echo '<h1>Hackers Country List</h1>
				<div class="row th">
					<div class="col w60">Country</div>
					<div class="col w40">Hackers</div>
				</div>';
			while ($row = mysqli_fetch_assoc($result)) {
				$counter ++;
			echo '
					<div class="row hr-light">
						<div class="col w5">'.'#'.$counter.'</div><div class="col w55"><img src="./images/flags/'.$row['country'].'.png" title="'.$row['name'].'" /> '.$row['name'].'</div>
						<div class="col w40">'.$row['numHackers'].'</div>
					</div>	
			';
			}
?>
