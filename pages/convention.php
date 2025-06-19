<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$request_nextconvention = $hackerdata['convention_last'] + 1;
	$needed_level4requested = $request_nextconvention * ($maxlevel / ($maxskill / $skillslots_per_convention));
	$message = "";
		
	if (EP2Level(GetHackerEP($hackerdata['id'])) < $needed_level4requested) $message = "You need be level $needed_level4requested to visit the next convention.";
	if ((($hackerdata['convention_last'] * $skillslots_per_convention) - $hackerdata['skill']) > 0) $message = "You are still gaining skill from ConDef #".$hackerdata['convention_last']."<br>".ShowProgress($hackerdata['skill'] -(($hackerdata['convention_last']-1) * $skillslots_per_convention), $skillslots_per_convention);
	if ($hackerdata['skill'] >= $maxskill) $message = "There is no more skill for you to gain.";
	
	if ($message == "") {
		$fee = 5000;
		for ($i = 0; $i <= $request_nextconvention; $i ++) $fee = round($fee * 1.3);
		$message = '<p>You are experienced enough to visit the next hacker convention, so please sign up.<br>
		The signup fee for the upcoming ConDef is: '.$currency.number_format($fee).'<br>
		<br>
		<form method="POST" action="index.php">
			<input type="hidden" name="h" value="doconvention">
			<input type="submit" value="Visit ConDef #'.$request_nextconvention.'">
		</form></p>';			 
	}	
?>
		<h1>ConDef Convention Signup</h1>
			<p><img src="images/convention.jpg" align="right" title="ConDef Hacker Convention" />
			Welcome to the ConDef Convention Signup page.<br>
			<br>
			ConDef is a hacker conference on which various security experts will give presentations about tons of hacking related subjects. Visiting these conferences will unlock skill potential. This potential will transform into actual skill when you do system and server hacks. And more skill means you will become a more efficient hacker.<br>
			<br>
			<strong><?php echo $message; ?></strong>
			<br>			
			</p>