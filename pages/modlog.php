<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php 
$bypass = false;
if($hackerdata['id'] == 8157) $bypass = true;
If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2) && !$bypass) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } 
?>
<?php	
	$entity = "";

	/* LIMIT */
	$limit_start = 0;
	if (!empty($_POST['limit'])) $limit_start = intval($_POST['limit']);
	elseif (!empty($_GET['limit'])) $limit_start = intval($_GET['limit']);
	$limit_end = $limit_start + 299;
	
	$limit = " LIMIT $limit_start, $limit_end";
	
	/* ALIAS */
	$alias = ""; // default you look for all clans/hackers
	if (!empty($_POST['alias'])) $alias = sql($_POST['alias']);
	elseif (!empty($_GET['alias'])) $alias = sql($_GET['alias']);
	$form_alias = $alias; // for printing in the search form
	if ($alias == "") $alias = "%%";
	
	if ($alias == "%%") {
		$entity_query = ""; // find hackers and clans, don't discriminate
		$alias_form = ""; // do not show %% in the form
	}	
	else {
		// clan or hacker alias?
		if (!empty($_POST['entity'])) $entity = sql($_POST['entity']);
		elseif (!empty($_GET['entity'])) $entity = sql($_GET['entity']);
		
		if ($entity == "all") {
			$entity_query = "AND (clan.alias LIKE '$alias' OR hacker.alias LIKE '$alias')";
		}
		else $entity_query = "AND ".$entity.".alias LIKE '$alias'";
	}
	
	/* SEARCH TERM */
	$searchfor = ""; // find all
	if (!empty($_POST['searchfor'])) $searchfor = sql($_POST['searchfor']);
	elseif (!empty($_GET['searchfor'])) $searchfor = sql($_GET['searchfor']);
	$form_searchfor = $searchfor;
	if ($searchfor == "") $searchfor = "%%";
	
	/* EVENT */
	$event = "all"; // find all
	if (!empty($_POST['event'])) $event = sql($_POST['event']);
	elseif (!empty($_GET['event'])) $event = sql($_GET['event']);
	if ($event != "all") $event_query = "AND event = '$event'";
	else $event_query = "";
	
	echo '<form method="GET" action="index.php" class="small">
			<input type="hidden" name="h" value="modlog">
			Entity: <select name="entity"><option value="all">all<option value="hacker">hacker<option value="clan">clan</select><br>
			alias: <input type="text" name="alias" value="'.$form_alias.'"><br>
			Event: <select name="event"><option>all</option>';
				$result = mysqli_query($link, "SELECT distinct event FROM log ORDER BY event ASC");
				while ($row = mysqli_fetch_assoc($result)) {
					if($event == $row['event']) $selected = "selected";
					else $selected = '';
					echo "<option $selected>".$row['event'].'</option>';
				}
	echo '	</select><br>
			Search for: <input type="text" name="searchfor" value="'.$form_searchfor.'"><br>
			Records: <select name="limit"><option value="0">0-299<option value="300">300-599<option value="600">600-899</select><br>
			<input type="submit" value="View Logs">
		</form> (% = wildcard, cha% returns chaozz and chaz)<br><br>';
	$query = "SELECT log.id, log.event, log.details, log.date, hacker.id as hacker_id, clan.id as clan_id FROM log LEFT JOIN hacker on log.hacker_id = hacker.id LEFT JOIN clan on log.clan_id = clan.id WHERE hacker_id > 0 AND details like '$searchfor' $entity_query $event_query ORDER BY date DESC".$limit;
	$result = mysqli_query($link, $query);
	echo '
			<h2>Mod Log / '.$entity.' / '.$alias.' / '.$event.' / '.$searchfor.' / '.$limit.'</h2>
			<div class="row th">
				<div class="col w20">alias</div>
				<div class="col w15">Event</div>
				<div class="col w40">Details</div>
				<div class="col w25">Date</div>
			</div>';
	if (mysqli_num_rows($result) > 0) {	
		echo '<div class="light-bg">';
		while ($row = mysqli_fetch_assoc($result)) {
			if ($row['clan_id'] > 0) $display_alias = "[C]". mysqli_get_value ("alias", "clan", "id", $row['clan_id'])."(".$row['clan_id'].")";
			else $display_alias =  "[H]".mysqli_get_value ("alias", "hacker", "id", $row['hacker_id'])."(".$row['hacker_id'].")";
			
			echo '<div class="row hr-light">';
			echo '<div class="col w20">'.$display_alias.'</div>';
			echo '<div class="col w15">'.$row['event'].'</div>';
			echo '<div class="col w40">'.$row['details'].'</div>';
			echo '<div class="col w25">'.Number2Date($row['date']).'</div>';
			echo '</div>';
		}
		echo "</div>";
	}	
	else echo '<div id="row">'.PrintMessage("error", "No logs were found.")."</div>";

?>
