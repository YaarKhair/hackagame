<?php 
	include ("modules/permissions.php");
    PrintMessage("info", "Place your own ad on HF and reach the entire community.<br><br>[list][li]The price for 1 ad is $currency".number_format($ad_price)."[/li][li]The ad will be visible for 1 hour[/li][li]Ad blocks always start on the whole hour, for example 17:00[/li][li]Ads can only be about HF related stuff. Spam, bad language or other abuse is unwanted and will result in bans[/li][li]If you made an error, report that to a member of the game administration, they can remove ads[/li][li]You can place $daily_ads ads per day[/li][li]You can only place an ad up to 7 days in the future[/li][/list]");
    $ad_date = date($date_format, strtotime("+1 hour"));
    $year = substr($ad_date,0,4);
    $month = substr($ad_date,4,2);
    $day = substr($ad_date,6,2);
    $hour = substr($ad_date,8,2);
    if ($hackerdata['ads'] > 0 || $is_staff) {
?>    
    <h2>Place an ad</h2>
    <form method="post" action="index.php" class="light-bg">
        <input type="hidden" name="h" value="doad">
		<div class="row">
			<div class="col w15">Date:</div>
			<div class="col w85">
				<div class="row">
					<div class="col w20"><label for="year">Year </label></div>
					<div class="col w80"><input id="year" type="text" name="year" maxlength="4" size="4" value="<?php echo $year; ?>"></div>
				</div>
				<div class="row">
					<div class="col w20"><label for="month"> Month </label></div>
					<div class="col w80"><input id="month" type="text" name="month" maxlength="2" size="2" value="<?php echo $month; ?>"></div>
				</div>
				<div class="row">
					<div class="col w20"><label for="day">Day </label></div>
					<div class="col w80"><input id="day" type="text" name="day" maxlength="2" size="2" value="<?php echo $day; ?>"></div>
				</div>
				<div class="row">
					<div class="col w20"><label for="hour">Hour </label></div>
					<div class="col w80"><input id="hour" type="text" name="hour" maxlength="2" size="2" value="<?php echo $hour; ?>"></div>
				</div>
				<div class="row">
					<div class="col w20"><label for="minute">Minute</label></div>
					<div class="col w80"><input id="minute" type="text" name="minute" maxlength="2" size="2" value="00" readonly></div>
				</div>
			</div>
		</div>
		<div class="row"><div class="col w15" style="vertical-align: middle"><label for="message">Message:</label></div><div class="col w85"><input class="w100i" id="message" type="text" name="message" maxlength="120"></div></div>
		<div class="row"><div class="col w15">Cost:</div><div class="col w85">
			<select name="pay">
				<option value="money"><?php echo $currency.number_format($ad_price); ?></option>
				<option value="hackpoints"><?php echo $ad_hp; ?>HP</option>
			</select>
		</div>	
        <div class="row"><td colspan="2"><input type="submit" value="Buy ad"></div></div>
    </form>
    <br>
    <br>
<?php
	}
	else PrintMessage("Error", "You are over your daily ads limit of $daily_ads");
	
	echo "<h2>Already sold ad blocks</h2>";
    $ad_date = substr($now,0,10);
    $result = mysqli_query($link, "SELECT ad.id, ad.date, ad.message, hacker.alias FROM ad LEFT JOIN hacker ON ad.hacker_id = hacker.id WHERE date >= '$ad_date' ORDER by DATE ASC");
    if (mysqli_num_rows($result) >0)
        while ($row = mysqli_fetch_assoc($result)) {
            $ad_date = $row['date'];
            $year = substr($ad_date,0,4);
            $month = substr($ad_date,4,2);
            $day = substr($ad_date,6,2);
            $hour = substr($ad_date,8,2);
            echo "<div class='row'><div class='col w50'>$year-$month-$day starting at $hour:00</div>";
            if (InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) echo "<div class='col w50'>[<a class=\"red\" href=\"?h=doadminaction&action=removead&id={$row['id']}\">X</a>] <strong>{$row['alias']}</strong> - {$row['message']}</div>";
            echo "</div>";
        }
    else echo "<div class='row'><div class='col w100'>No ads sold yet</div></div>";
?>