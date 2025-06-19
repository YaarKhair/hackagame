<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<script type="text/javascript" src="modules/jscolor/jscolor.js"></script>
<?php
	if ($hackerdata['clan_council'] != 1) return "You are not in the council";
	$_SESSION['doeditclan'] = 1;
	$result = mysqli_query($link, "SELECT * FROM `clan` WHERE `id` = ". $hackerdata['clan_id']);
	$row = mysqli_fetch_assoc($result);
?>		
	<div class="accordion">
    	<input id="clansettings" type="checkbox" class="accordion-toggle">
		<label for="clansettings">Clan Settings</label>
		<div class="accordion-box">
			<form enctype="multipart/form-data" method="POST" action="index.php">
			<input type="hidden" name="h" value="doeditclan">
			<div class="row">
				<div class="col w30">New Bank Password <div class="note">Fill this form and the one below if you want to change your clan password</div></div>
				<div class="col w70"><input type="password" name="pass1" class="icon pass"></div>
			</div>
			<div class="row">
				<div class="col w30">New Bank Password <div class="note">Again</div></div>
				<div class="col w70"><input type="password" name="pass2" class="icon pass"></div>										
			</div>
			<div class="row">
				<div class="col w30">Short Tag</div>
				<div class="col w70"><input name="shorttag" value="<?php echo $row['shorttag']; ?>" type="text" size="3" maxlength="3"></div>
			</div>
			<div class="row">
				<div class="col w30">Clan Color</div>
				<div class="col w70"><input name="color" class="color" value="<?php echo $row['color']; ?>" size="6" maxlenght="6"></div>
			</div>
			<div class="row">
				<div class="col w30">Extra Info</div>
				<div class="col w70"><textarea cols="70" rows="30" name="extra_info" class="resizeable w100i h200"><?php echo br2nl($row['extra_info']); ?></textarea></div>
			</div>			
			<div class="row">
				<div class="col w30">New Clan Picture <div class="note">Allowed Extensions: <?php echo implode(",", $allowed_avatar_extensions); ?></div></div>
				<div class="col w70"><input type="file" name="avatar"></div>
			</div>
			<div class="row">
				<div class="col w30">Remove Avatar</div>
				<div class="col w70">
					<select name="remove_avatar">
						<option value="on">Yes</option>
						<option value="off" selected="selected">No</option>
					</select>
				</div>
			</div>
		</div>
	</div>
	<?php if($hackerdata['donator_till'] > $now) { ?>
	<div class="accordion">
		<input id="premium_clan_settings" type="checkbox" class="accordion-toggle">
		<label for="premium_clan_settings">Premium Clan Settings</label>
		<div class="accordion-box">
			<div class="light-bg">
				<div class="row">
					<div class="col w30">New alias</div>
					<div class="col w70"><input type="text" name="alias" maxlength="20" class="icon user"><?php if ($row['nextalias_date'] > $now) PrintMessage ("Error", "You can change your clans' alias again at ". Number2Date($row['nextalias_date'])); ?></div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
	<div id="shop-wrapper">
		<div class="shop-cart">
			<div class="row th">
				<div class="col w100">Save Changes</div>
			</div>
			<div class="row">
				<div class="col w30">Current Clan Password</div>
				<div class="col w70"><input type="password" name="pass0" class="icon pass"></div>
			</div>
			<div class="row">
			<input type="submit" value="Save Settings">
			</div>
		</div>
	</div>
</form>