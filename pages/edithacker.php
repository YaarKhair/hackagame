<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	$_SESSION['doedithacker'] = 1;
?>
<script type="text/javascript" src="modules/jscolor/jscolor.js"></script>
		<form enctype="multipart/form-data" method="POST" action="index.php">
			<div class="row">
				<!--- Password change accordion -->
				<div class="accordion">
					<input id="acc-mgmt" type="checkbox" class="accordion-toggle">
					<label for="acc-mgmt">Account Management</label>
					<div class="accordion-box">
						<div class="light-bg">
							<div class="row">
								<input type="hidden" name="h" value="doedithacker">
								<input type="hidden" name="MAX_FILE_SIZE" value="500000">
								<div class="col w50">New Password (If you want to change it)</div>
								<div class="col w50"><input type="password" name="pass1" class="icon pass"></div>
							</div>
							<div class="row">
								<div class="col w50">New Password (Again)</div>
								<div class="col w50"><input type="password" name="pass2" class="icon pass"></div>
							</div>
							<div class="row">
								<div class="col w50">Country</div>
								<div class="col w50">
									<select name="country">
										<?php
										$result = mysqli_query($link, "SELECT * FROM country ORDER BY name ASC");
											if (mysqli_num_rows($result) != 0) {
											while ($row = mysqli_fetch_assoc($result)) {
												$selected = "";
												if (strtolower($row['code']) == $hackerdata['country']) $selected = " SELECTED"; // select the current country
												if (file_exists('images/flags/'.strtolower($row['code']).'.png')) echo '<option value="'.strtolower($row['code']).'"'.$selected.'>'.$row['name'];
											}
										}
										?>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Avatar <div class="note">(Allowed extensions: <?php echo implode(", ", $allowed_avatar_extensions); ?>)</div></div>
								<div class="col w5">
									<input type="file" name="avatar" size="30">
								</div>
							</div>
							<div class="row">
								<div class="col w50">Do you want to remove your avatar?</div>
								<div class="col w50">
									<select name="remove_avatar">
										<option value="on">Yes</option>
										<option value="off" selected>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Custom Profile Text</div>
								<div class="col w50"><textarea cols="70" rows="30" name="extra_info" class="resizeable w100i h200"><?php echo br2nl($hackerdata['extra_info']); ?></textarea></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="row">
				<!---- Other options accordion ---->
				<div class="accordion">
					<input id="other-options" type="checkbox" class="accordion-toggle">
					<label for="other-options">Other Options</label>
					<div class="accordion-box">
						<div class="light-bg">
							<div class="row">
								<div class="col w50"><label for="tutorial_messages">Do you want to get tutorial messages?</label></div>
								<div class="col w50">
									<?php if ($hackerdata['show_tutorial'] == 1) { $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected";} ?>
									<select name="show_tutorial">
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50"><label for="show_tooltips">Do you want to see tips?</label></div>
								<div class="col w50">
									<?php if ($hackerdata['show_tooltips'] == 1){ $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected";}?>
									<select name="show_tutorial">
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Do you want to be alerted when you are mentioned in chat?</div>
								<div class="col w50">
									<?php if ($hackerdata['chat_alert'] == 1){ $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected";}?>
									<select name="chat_alert">
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Hibernate (Between 7 and 21 days)</div>
								<div class="col w50"><input type="text" name="hybernate" min="7" max="21" class="icon sleep" onclick="alert('WARNING: THIS CANNOT BE UNDONE');"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if($hackerdata['donator_till'] > $now) { ?>
			<div id="row">
				<!---- Premium hackers accordion --->
				<div class="accordion">
					<input id="premium-options" type="checkbox" class="accordion-toggle">
					<label for="premium-options">Premium Options</label>
					<div class="accordion-box">
						<div class="light-bg">
							<div class="row">
								<div class="col w50">New alias</div>
								<div class="col w50">
									<?php 
									if($hackerdata['nextalias_date'] > $now) PrintMessage("error", "You can change your alias again at ".Number2Date($hackerdata['nextalias_date']));
									else echo '<input type="text" name="alias" class="icon user" maxlength="20">';
									?>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Chat text color</div>
								<div class="col w50"><input class="icon colorpicker" type="text" name="color" class="color" value="<?php echo $hackerdata['chat_color']; ?>"></div>
							</div>
							<div class="row">
								<div class="col w50">Do you want to see advertisments?</div>
								<div class="col w50">
									<select name="show_ads">
										<?php if ($hackerdata['show_ads'] == 1) { $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected"; } ?>
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Do you want to send a copy of your IMs to your email? <div class="note">(Excluding iBot and System)</div></div>
								<div class="col w50">
									<select name="cc2mail">
										<?php if ($hackerdata['cc2mail'] == 1) { $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected"; }?>
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Do you want a sound to be played when you receive new mail?</div>
								<div class="col w50">
									<select name="sound_email">
										<?php if ($hackerdata['sound_email'] == 1) { $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected"; } ?>
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Do you want your statistics to be publicly viewable?</div>
								<div class="col w50">
									<select name="publicstats">
										<?php if ($hackerdata['publicstats'] == 1) { $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected"; } ?>
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Do you want to see your online friends in a slide-in-panel? <div class="note">(Like the CTF or Online Staff)</div></div>
								<div class="col w50">
									<select name="show_friends">
										<?php if ($hackerdata['show_friends'] == 1) { $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected"; } ?>
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col w50">Do you want to see your online foes in a slide-in-panel? <div class="note">(Like the CTF or Online Staff)</div></div>
								<div class="col w50">
									<select name="show_foes">
										<?php if ($hackerdata['show_foes'] == 1) { $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected"; } ?>
										<option value="on" <?php echo $yes; ?>>Yes</option>
										<option value="off" <?php echo $no; ?>>No</option>
									</select>
								</div>
							</div>
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
						<?php if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) { ?>
						<div class="row">
							<div class="col w50">Do you want to be invisible ingame?</div>
							<div class="col w50">
								<?php if($hackerdata['invisible'] == 1) { $yes = " selected"; $no = ''; } else { $yes = ""; $no = "selected"; } ?>
								<select name="invisible">
									<option value="on" <?php echo $yes; ?>>Yes</option>
									<option value="off" <?php echo $no; ?>>No</option>
								</select>
							</div>
						</div>
						<?php } ?>
						<div class="row">
							<div class="col w50">Current Password</div>
							<div class="col w50"><input class="icon pass" type="password" name="pass0"></div>
						</div>
						<div class="row">
							<input type="submit" value="Save changes">
						</div>
				</div>
			</div>
		</form>