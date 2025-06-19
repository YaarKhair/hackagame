<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
	<table class="info" width="40%">
		<thead>
			<tr><th colspan="2">Request a new password</th></tr>
		</thead>
		<tbody>
			<form method="post" action="index.php" name="hf_form">
				<input type="hidden" name="h" value="doemailpass">
				<tr><th>Email address:</th><td><input type="text" size="40" maxlength="50" name="email" value="<?php if (isset($_SESSION['email'])) echo $_SESSION['email']; ?>"></td></tr>
				<tr><th colspan="2"><input type="submit" value="Request New Password"></th></tr>
			</form>	
		</tbody>
	</table>	
	<br><br>
	<script type="text/javascript">document.hf_form.email.focus();</script>	