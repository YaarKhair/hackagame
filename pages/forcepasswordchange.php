<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	if($_SESSION['loaded'] != 1) die();
	if ($hackerdata['force_passchange'] == 1) {
		$_SESSION['loaded'] = 0; // prevent refresh on save page
		$_SESSION['doedithacker'] = 1;
		echo '
			<table width="60%">
				<thead>
					<tr>
						<th colspan="2">Updated Security > Forced password change</th>
					</tr>
				</thead>
				<tbody>
					<form method="POST" action="index.php">
						<input type="hidden" name="h" value="doedithacker">
						<input type="hidden" name="forced" value="1">
					<tr>
						<th>New Password</th>
						<td><input type="password" name="pass1" size="15" maxlength="15"></td>
					</tr>
					<tr>
						<th>New Password<br>(again)</th>
						<td><input type="password" name="pass2" size="15" maxlength="15"></td>
					</tr>
					<tr><th colspan="2"><hr></th></tr>
					<tr>
						<th>Current Password<br></th>
						<td><input type="password" name="pass0" size="15" maxlength="15"><input type="submit" value="Save Changes"></td>
					</tr>
					</form>
				</tbody>
			</table>	
		';
	}
	else PrintMessage ("Error", "Forced password change is not active on your account", "40%");	
?>