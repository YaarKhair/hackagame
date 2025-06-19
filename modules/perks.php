<?php
	if(!isset($hacker_perk_id)) $hacker_perk_id = $hackerdata['id'];
	
	if ($perk_code == "PERK_HIDEFAILIP")
		$perk_val = 'GH0ST.1N.TH3.W1R3S';
	else	
		If(EquippedPerk($perk_hackerid, $perk2check))
			$perk_val = mysqli_get_value("efficiency", "product", "code", $perk2check, false);
?>