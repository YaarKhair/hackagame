<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	include ("modules/permissions.php");

	$search_term = "";
	$search_field = "alias";

	if(!empty($_POST['search']) && strlen($_POST['search']) >= 3) $search_term = sql($_POST['search']);
	else return "Search term is either empty or too short (minimum 3 characters)";

	// if you're searching for a specific entity, you can use the following syntax:
	// somealias (gets internally translated to alias:somealias)
	// ip:127.0.0.1 (staff only!)
	// real_ip:127.0.0.1 (staff only!)
	// email:chaozz@hackerforever.com (staff only!)
	
	if (strpos($search_term, ":", 0) === false) $search_term = "alias:".$search_term; // if you ommit the : you are either a player, or looking for hacker/clan alias
	else if (!$is_staff) return "Invalid syntax"; // using search syntaxing? then you need to be staff
		
	$part = explode(":", $search_term);
	$valid_entitites = array ("alias", "ip", "email", "real_ip");
	if (!in_array($part[0], $valid_entitites)) return "Invalid syntax";
		
	$search_field = $part[0];
	$search_term = $part[1];
	$num_results = 0;

	echo "<h1 class='mv10'>Search Results</h1>";
	echo "<div class='row'>";

	if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) $filter = '';
	else $filter = "AND banned_date = '0' AND email NOT LIKE '**%'"; // not staff? then lets filter banned and disabled accounts

	$hacker_result = mysqli_query($link, "SELECT id, alias FROM hacker WHERE $search_field LIKE '%$search_term%' AND activationcode = '' $filter LIMIT 0,20");
	if(mysqli_num_rows($hacker_result) > 0) {
		$num_results += mysqli_num_rows($hacker_result);
		echo "<div class='col w50'>";
		echo "<h2>Hackers (Limit: 20)</h2>";
			while($row = mysqli_fetch_assoc($hacker_result)) echo "<div class='row hr-light'><a href='?h=profile&id={$row['id']}'>{$row['alias']}</a></div>";
		echo "</div>";
	}
	
	// if you're search for a name, it can be a clan too
	if ($search_field == "alias")
	{
		$clan_result = mysqli_query($link, "SELECT id, alias FROM clan WHERE $search_field LIKE '%$search_term%' AND active = 1 LIMIT 0,20");
		if(mysqli_num_rows($clan_result) > 0) {
			$num_results += mysqli_num_rows($clan_result);
			echo "<div class='col w50'>";
			echo "<h2>Clans (Limit: 10)</h2>";
				while($row = mysqli_fetch_assoc($clan_result)) echo "<div class='row hr-light'><a href='?h=claninfo&id={$row['id']}'>{$row['alias']}</a></div>";
			echo "</div>";
		} 
	}	
	echo "</div>";
	PrintMessage ("info", "Your search query has resulted in $num_results results.");
?>
	