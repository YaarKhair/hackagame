<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php

	// Get the ID
	$hacker_id = $hackerdata['id'];
	if((InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) && !empty($_GET['hacker_id'])) $hacker_id = intval($_GET['hacker_id']);
	
	// Get the perks
	$result = mysqli_query($link, "SELECT equip_date, product_id, id FROM perks WHERE hacker_id = $hacker_id ORDER BY equip_date DESC");
	$perks = array();
	while($row = mysqli_fetch_assoc($result)) {
		$perks[] = array("title" => "<a href='?h=doperk&action=delete&id={$row['id']}'><span class='red'>X</span></a>  ".mysqli_get_value("title", "product", "id", $row['product_id']), "expiry_date" => date($date_format, strtotime("{$row['equip_date']} + 7 days")));
	}
	
	// Pad the array so you can also see empty slots
	$num_perks = count($perks);
	for($i = ($num_perks + 1); $i <= AllowedNumPerks($hacker_id); $i++) {
		$perks[] = array("title" => "Slot #$i", "expiry_date" => "N/A");
	}
	// Display the perks
	echo "
			<h1>Perk Slots</h1>
			<div class='row th light-bg'>
				<div class='col w70'>Perk</div>
				<div class='col w30'>Expiry Date</div>
			</div><div class='dark-bg'>";
			
	foreach($perks as $perk) {
		echo "<div class='row hr-light'>";
		echo "<div class='col w70'>".$perk['title'].'</div>';
		if($perk['expiry_date'] != 'N/A') $date = Number2Date($perk['expiry_date']);
		else $date = $perk['expiry_date'];
		echo '<div class="col w30">'.$date.'</div>';
		echo '</div>';
	}
	echo '</div>';
?>