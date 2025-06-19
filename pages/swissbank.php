<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
		$hash = "";
		if (!empty($_GET['hash'])) $hash = sql($_GET['hash']);
		
		echo '<table class="borderless"><tr><td><img src="images/swiss.png" align="left" /></td><td><h1>&nbsp;Swiss Secure Bank</h1></td></tr></table>';
		
		$result = mysqli_query($link, "SELECT amount, expire_date FROM swissbank WHERE hash = '$hash'");
		if (mysqli_num_rows($result) == 0) {
			Return "This transfer is not valid";
		}
		else {
			$row = mysqli_fetch_assoc($result);
			if ($row['expire_date'] < $now) Return "This link expired on ".Number2Date($row['expire_date']);
			else
			{
				BankTransfer($hackerdata['id'], "hacker", $row['amount'], "Swiss Secure Bank money transfer");
				// delete the transfer money
				$result = mysqli_query($link, "DELETE FROM swissbank WHERE hash = '$hash'");
				PrintMessage ("Success", "Money transfer successful", "40%");
			}
		}
?>