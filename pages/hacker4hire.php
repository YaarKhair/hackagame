<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php	
	$_SESSION['loaded'] = 0; // anti refresh
	echo "<h1>Hacker4Hire</h1>";
	echo '<div class="row light-bg mv10">';
	echo "<div class='col w35'><img src=\"images/H4H.png\" class='frame rounded'/></div><div class='col w65'>If you don't want to get your own hands dirty, then hiring a hacker is the best option. You will have to pay the hacker a fair amount, but he or she will get the job done most of the time. But be careful, if the job fails, the FBI might interrogate the hacker. If he spills his guts, you're going to jail too.</div>";
	echo "</div>";
	
	// show your current hire contracts
	$result	= mysqli_query($link, "SELECT * FROM log WHERE hacker_id = ".$hackerdata['id']." AND event = 'hire-job' AND date > '".$now."' ORDER BY id ASC");
	if (mysqli_num_rows($result) > 0) {
		echo '<div class="accordion">
              	<input id="hired_hackers" type="checkbox" class="accordion-toggle" checked>
                <label for="hired_hackers">Hackers currently working for you</label>
                <div class="accordion-box">';
					while ($row = mysqli_fetch_assoc($result)) echo '<div class="row hr-light"><div class="col w70">'.$row['details'].'</div><div class="col w30">'.Seconds2Time(SecondsDiff($now,$row['date'])).'</div></div>';
           echo'</div>
             </div><br><br>';
	}
	// show hackers you can hire
	$query = "SELECT * FROM hirejobs WHERE price{$hackerdata['network_id']} <> 0";
	$result = mysqli_query($link, $query);
	$counter = 0;
	
	while ($row = mysqli_fetch_assoc($result)) {
		echo '
			<FORM NAME="hf_form'.$counter.'" ACTION="index.php" METHOD="POST">
				<INPUT TYPE="hidden" NAME="h" VALUE="dohacker4hire">
				<INPUT TYPE="hidden" NAME="hire_id" VALUE="'.$row['id'].'">
				<div class="accordion">
                	<input id="hirejob_'.$counter.'" type="checkbox" class="accordion-toggle">
                    <label for="hirejob_'.$counter.'">'.$row['title'].'</label>
                    <div class="accordion-box">
						<div class="light-bg">
							<div class="row hr-light">
								<div class="col w50">Details</div>
								<div class="col w50">'.$row['description'].'</div>
							</div>
							<div class="row hr-light">
								<div class="col w50">Cost</div>
								<div class="col w50">
									<select name="pay">
										<option value="money">'.$currency.number_format($row['price'.$hackerdata['network_id']]).'</option>
										<option value="hackpoints">'.$row['hackpoints'].'HP'.'</option>
									</select>
								</div>
							</div>'
							.$row['inputfields'].
							'
							<div class="row hr-light">
								<input type="submit" value="Hire Hacker(s)">
							</div>
						</div>
                    </div>
                </div>
				</form>
		';	
		$counter ++;
	}
	echo '<script type="text/javascript">document.hf_form0.username.focus();</script>';	
?>
