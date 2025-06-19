<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<script type="text/javascript" src="modules/jscolor/jscolor.js"></script>
<?php
	if($hackerdata['network_id'] != 2) 
	{
		PrintMessage ("error", "You are connected to n00bNET. The function you are trying to use is unavailable from this network.", "40%");
	}
	else 
	{
		if ($hackerdata['clan_id'] == 0) 
		{
			// do you have a gateway on your hdd?
			$result = mysqli_query ($link, "SELECT inventory.id FROM inventory LEFT JOIN product ON inventory.product_id = product.id WHERE inventory.hacker_id = {$hackerdata['id']} AND code LIKE 'GATEWAY_%'");
			if (!$result || mysqli_num_rows($result) == 0) return "ERROR: You don't have gateway software on your HDD!";
			
			$_SESSION['startclan'] = 1;
?>
			<div class="row th">
					<div class="col w100">Start New Clan</div>
				</div>
				<div class="light-bg">
				<form method="POST" action="index.php">
					<input type="hidden" name="h" value="dostartclan">
				<div class="row mv5">
					<div class="col w20">Clan Name</div>
					<div class="col w80"><input type="text" name="alias" size="20" maxlength="20"></div>
				</div>
				<div class="row mv5">
					<div class="col w20">Short Tag</div>
					<div class="col w80">[<input type="text" name="shorttag" size="3" maxlength="3">]</div>
				</div>
				<div class="row mv5">
					<div class="col w20">Gateway Size</div>
					<div class="col w80">
					<select name="gateway_id">
<?php				
					$result = mysqli_query ($link, "SELECT product.* FROM inventory LEFT JOIN product ON inventory.product_id = product.id WHERE inventory.hacker_id = {$hackerdata['id']} AND product.code LIKE 'GATEWAY_%'");
					if (mysqli_num_rows($result) > 0) 
						while ($row = mysqli_fetch_assoc($result)) 
							echo '<option value="'.$row['id'].'"> '.$row['title'].', '.$row['efficiency'].' members</option>';
?>			
					</select>		
					</div>
				</div>
				<div class="row mv5">
					<div class="col w20">Bank Password</div>
					<div class="col w80"><input type="password" name="pass1" size="15" maxlength="15"></div>
				</div>
				<div class="row mv5">
					<div class="col w20">Bank Password<br>(again)</div>
					<div class="col w80"><input type="password" name="pass2" size="15" maxlength="15"></div>
				</div>
				<div class="row mv5">
					<div class="col w20">Clan color</div>
					<div class="col w80"><input name="color" class="color"> <style:color="red">BLACK OR VERY DARK=BAN!</style></div>
				</div>
				<div class="row mv5">
					<input type="submit" value="Make clan">
			</div>	
			</form>
<?php			
		}
		else PrintMessage("error", "First you need to leave your current clan.");
	}	
?>