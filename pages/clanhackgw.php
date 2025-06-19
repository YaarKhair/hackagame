<?php
	$goal_id = 1;
	// for gw hacks we need a clan_id
	if (!empty ($_GET['clan_id'])) $clan_id = intval ($_GET['clan_id']);
	else $clan_id = 0;
	$return_value = include ("./pages/clanhack.php");
	if ($return_value != 1) return $return_value;
?>	
