<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if (!empty($_GET['code'])) 
		$code = sql($_GET['code']);
	else return "Wrong code. Please make sure you copy the entire link in the activation email";

	if (!empty($_GET['id']))
		$id = intval($_GET['id']);
	else return "Wrong ID. Please make sure you copy the entire link in the activation email";
	
	// check if we can find this account
	$result = mysqli_query($link, "SELECT id, activationcode FROM hacker WHERE id = $id");
	if (mysqli_num_rows($result) == 0) 
		return "Wrong ID. Please make sure you copy the entire link in the activation email";

	// activate the account
	$row = mysqli_fetch_assoc($result);
	if ($code == $row['activationcode']) {
		$result = mysqli_query($link, "UPDATE hacker SET activationcode = '', last_click = '$now' WHERE id = $id");
		PrintMessage ("Success", "Account activated. To start playing <a href=\"".$gameurl."/guest.php#login\">click here</a>"); 
		PlayerTutorial($id, 0); // give them the first email with some general info
	}
	else {
		if ($row['activationcode'] == "") {
			PrintMessage ("Error", "This account is already activated! To start playing <a href=\"".$gameurl."/guest.php#login\">click here</a>");
		}
		else {
			PrintMessage ("Error", "Wrong code. Please make sure you copy the entire link in the activation email");
		}
	}	
?>