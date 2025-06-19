<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$request_nextconvention = $hackerdata['convention_last'] + 1;
	$needed_level4requested = $request_nextconvention * ($maxlevel / ($maxskill / $skillslots_per_convention));
	$error = "";
	
	if (EP2Level(GetHackerEP($hackerdata['id'])) < $needed_level4requested) $error = "You need be level $needed_level4requested to visit the next convention.";
	if ((($hackerdata['convention_last'] * $skillslots_per_convention) - $hackerdata['skill']) > 0) $error = "You are still gaining skill from the last convention.";
	if ($hackerdata['skill'] >= $maxskill) $error = "There is no more skill to gain.";
	
	$fee = 5000;
	for ($i = 0; $i <= $request_nextconvention; $i ++) $fee = round($fee * 1.3);
	if ($fee > $hackerdata['bankaccount']) $error = "You can not afford to pay the fee.";
	
	if ($error == "") {
		$result = mysqli_query($link, "UPDATE hacker SET convention_last = ".$request_nextconvention." WHERE id = ".$hackerdata['id']);
		BankTransfer($hackerdata['id'], "hacker", $fee * -1, "ConDef #".$request_nextconvention." fee");
		
		$message = 'You went to ConDef and learned a lot of new security related stuff. Your skill will now increase when you use this new knowledge.';			 
		PrintMessage ("Success", $message, "60%");
	}
	else PrintMessage ("Error", $error, "40%");	
?>