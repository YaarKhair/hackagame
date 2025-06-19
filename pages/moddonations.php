<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php if (!isset($mainpage)) if (!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>

<?php if (!empty($_POST['from'])) { ?>
<form method="POST" action="index.php">
	<input type="hidden" name="h" value="moddonations">
	<input type="text" name="from" value="<?php echo $now; ?>">
	<input type="text" name="to" value="<?php echo $now; ?>">
	<input type="submit" value="Calculate Donations">
</form>
<?php } ?>

<?php
	if (!empty($_POST['from']) || isset($mainpage))	{
		$donation_total = 0;
		
		if (!empty($_POST['from']))
			$sql = " AND details LIKE '%Donated%' AND date >= '".intval($_POST['from'])."' AND date <= '".intval($_POST['to'])."'";
		else
			$sql = " AND details LIKE '%Donated%' AND date LIKE '%".substr($now,0,6)."%'";
		
		$result = mysqli_query ($link, "SELECT details FROM log WHERE event='staff'".$sql); // all donations
		$number_of_donations = mysqli_num_rows($result);
		while ($row = mysqli_fetch_assoc ($result)) {
			$details = $row['details']; // get the details of each donation

			$part = explode ("Donated", $details); // split the string on the word onated, so we end up with: 15 [chaozz]
			
			$amount_part = trim($part[1]); // the amount, plus some extra stuff
			$amount = "";
			$stop_loop = false;
			$pos = 0;
			
			// lets filter out the amount. we simple start from the left of the string, and stop when we hit a non numeric value.
			while (!$stop_loop) {
				$sub = substr($amount_part, $pos, 1);
				if (is_numeric($sub)) $amount .= $sub;
				else $stop_loop = true;
				$pos++;
			}
			
			$donation_total += intval ($amount);
		}
		
		if (!empty($_POST['from']))
			PrintMessage ("info", "In the given period HF has recieved $number_of_donations donations, with a combined total of $currency$donation_total");
		else {
			if ($donation_total >= $donations_needed) $type = "success";
			else $type = "error";
			$percentage = round (($donation_total / $donations_needed) * 100);
			if ($percentage > 100) $percentage = 100;
			PrintMessage ($type, "This months donation status: $percentage %<br><br>HELP US! <a href=\"$gameurl/index.php?h=premiumhacker\">GET PREMIUM TODAY!</a>");
		}
	}
?>