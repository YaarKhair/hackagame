<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>

<div class="accordion">
	<input id="a2" type="checkbox" class="accordion-toggle">
    <label for="a2">Mod options</label>
	<div class="accordion-box">
		<div class="row">
			<div class="col w50">	
				<h3>Account Information</h3>
				<div class="light-bg">
					<div class="row">
						<!-- Affiliate -->
						<?php 
							$referrer = 'None';
							if($row['referral_id'] > 0) $referrer = mysqli_get_value("alias", "hacker", "id", $row['referral_id']);
						?>
						<div class="col w50">Referer:</div>
						<div class="col w50"><a href="?h=profile&id=<?php echo $row['referral_id']; ?>"><?php echo $referrer; ?></a></div>
					</div>

					<div class="row">
						<!-- Stats -->
						<div class="col w50">Stats:</div>
						<div class="col w50"><a href="?h=personalstats&hacker_id=<?php echo $row['id']; ?>">[View]</a></div>
					</div>

					<div class="row">
						<!-- Modlog -->
						<div class="col w50">Modlog:</div>
						<div class="col w50"><a href="?h=modlog&entity=hacker&alias=<?php echo $row['alias']; ?>">[View]</a></div>
					</div>

					<div class="row">
						<!-- Dupe score -->
						<div class="col w50">Duplicate Score:</div>
						<div class="col w50"><?php echo $row['duplicate_score']; ?> <a href="?h=modlog&entity=hacker&alias=<?php echo $row['alias']; ?>&event=staff&searchfor=duplicate%">[View]</a><a href="?h=doadminaction&action=resetdupe&id=<?php echo $row['id']; ?>">[Reset]</a></div>
					</div>

					<div class="row">
						<!-- bot score -->
						<div class="col w50">Bot Score:</div>
						<div class="col w50"><?php echo $row['bot_score']; ?> <a href="?h=modlog&entity=hacker&alias=<?php echo $row['alias']; ?>&event=staff&searchfor=%bot%">[View]</a><a href="?h=doadminaction&action=resetbot&id=<?php echo $row['id']; ?>">[Reset]</a></div>
					</div>

					<div class="row">
						<!-- Shit list -->
						<div class="col w50">SHIT list:</div>
						<div class="col w50"><a href="?h=modinterval&id=<?php echo $row['id']; ?>">[View]</a></div>
					</div>

					<div class="row">
						<!-- EP -->
						<div class="col w50">EP:</div>
						<div class="col w50"><?php echo number_format($row['ep']); ?></div>
					</div>
					
					<div class="row">
						<!-- bank -->
						<div class="col w50">Bank:</div>
						<div class="col w50"><?php echo $currency.number_format($row['bankaccount']); ?></div>
					</div>


					<div class="row">
						<!-- Skill -->
						<div class="col w50">Skill:</div>
						<div class="col w50"><?php echo number_format($row['skill']); ?></div>
					</div>

					<div class="row">
						<!-- Network -->
						<div class="col w50">Network:</div>
						<div class="col w50"><?php echo mysqli_get_value("name", "network", "id", $row['network_id']); ?></div>
					</div>

					<div class="row">
						<!-- Current Page -->
						<div class="col w50">Current page:</div>
						<div class="col w50"><?php echo $row['current_page']; ?></div>
					</div>

					<div class="row">
						<!-- Real IP -->
						<div class="col w50">Real IP:</div>
						<div class="col w50">
							<div class="row"><?php echo $row['real_ip']; ?></div>
							<?php
							// find IP friends :)
							$ip_result = mysqli_query($link, "SELECT id, alias FROM hacker WHERE id <> {$row['id']} AND real_ip = '".$row['real_ip']."'");
							$ip_friends = "";
								while ($row2 = mysqli_fetch_assoc($ip_result)) $ip_friends .= '<div class="row"><a href="?h=profile&id='.$row2['id'].'">'.$row2['alias'].'</a></div>';
							if ($ip_friends != "") echo '<div class="row">People also using this IP: </div>'.$ip_friends;
							?>
						</div>
					</div>

					<div class="row">
						<!-- Game IP -->
						<div class="col w50">Game IP:</div>
						<div class="col w50"><?php echo $row['ip']; ?></div>
					</div>

					<div class="row">
						<!-- System -->
						<div class="col w50">System:</div>
						<div class="col w50">
							<?php
								$system = mysqli_query($link, "SELECT product.title FROM system LEFT JOIN product ON system.product_id = product.id WHERE hacker_id = {$row['id']}");
								while($row_sys = mysqli_fetch_assoc($system)) echo $row_sys['title'].'<br>';
							?>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col w50">
				<h3>Account Management</h3>
				<div class="light-bg">
				
					<div class="row">
						<!-- Disable Account -->
						<div class="col w50">Disable / Enable</div>
						<div class="col w50">
							<form action="index.php" method="POST" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<select name="action">
									<option value="disable">Disable</option>
									<option value="enable">Enable</option>
								</select>
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Update">
							</form>
						</div>
					</div>
					
					<div class="row">
						<!-- Change Email -->
						<div class="col w50">Email:</div>
						<div class="col w50">
							<form action="index.php" method="POST" class="small">
								<input type="text" name="email" value="<?php echo $row['email']; ?>">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="changeemail">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Change Email">
							</form>
						</div>
					</div>
					
					<div class="row">
						<!-- Reset network -->
						<div class="col w50">Reset To Noobnet:</div>
						<div class="col w50">
							<form action="index.php" method="POST" class="small">
								<input type="submit" value="Reset to noobnet">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="resetnetwork">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
							</form>
						</div>
					</div>
					
					<div class="row">
						<!-- Change captcha -->
						<div class="col w50">Captcha:</div>
						<div class="col w50">
							<form action="index.php" method="POST" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="setcaptcha">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="text" name="till" value="<?php echo Number2Date($row['nextcaptcha_date']); ?>">
								<input type="submit" value="Set Captcha">
							</form>
						</div>
					</div>
					
					<div class="row">
						<!-- Clear Profile -->
						<div class="col w50">Clear Profile Text:</div>
						<div class="col w50">
							<form action="index.php" method="POST" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="clearprofile">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="text" name="reason" value="[reason]">
								<input type="submit" value="Clear Text">
							</form>
						</div>
					</div>
					
					<div class="row">
						<!-- Delete Avatar -->
						<div class="col w50">Delete avatar:</div>
						<div class="col w50">
							<form action="index.php" method="POST" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="deleteavatar">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="hidden" name="entity" value="hacker">
								<input type="text" name="reason" value="[reason]">
								<input type="submit" value="Delete">
							</form>
						</div>
					</div>
					
					<?php if($row['failed_logins'] > $bruteforce_limit) { ?>
					<div class="row">
						<!-- Remove lockout -->
						<div class="col w50">Remove lockout:</div>
						<div class="col w50">
							<form action="index.php" method="POST" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unlock">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Unlock">
							</form>
						</div>
					</div>
					<?php } ?>
					
					<?php if($row['prison_till'] > $now) { ?>
					<div class="row">
						<!-- Prison break -->
						<div class="col w50">Prison break:</div>
						<div class="col w50">
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unprison">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Free From Prison">
							</form>
						</div>
					</div>
					<?php } ?>
					
					<div class="row">
						<!-- Jail -->
						<div class="col w50">Jail:</div>
						<div class="col w50">
							<?php if($now > $row['jailed_till']) { ?>
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="jail">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="text" name="reason" value="[reason]"><br>
								<input type="text" name="till" value="<?php echo $now; ?>">
								<input type="submit" value="Jail">
							</form>
							<?php } else { ?>
							Jailed From: <?php echo Number2Date($row['jailed_from']); ?>
							Jailed Till: <?php echo Number2Date($row['jailed_till']); ?>						
							Reason: <?php echo $row['jailed_reason']; ?>						
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unjail">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Set Free">
							</form>
							<?php } ?>
						</div>
					</div>
					
					<div class="row">
						<!-- Chat and forum kick -->
						<div class="col w50">Chat & Forum Kick:</div>
						<div class="col w50">
							<?php if($now > $row['chatkick_till']) { ?>
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="kick">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="text" name="reason" value="[reason]"><br>
								<input type="text" name="till" value="<?php echo $now; ?>">
								<input type="submit" value="Kick">
							</form>
							<?php } else { ?>
							Kicked From: <?php echo Number2Date($row['chatkick_from']); ?>
							Kicked Till: <?php echo Number2Date($row['chatkick_till']); ?>						
							Reason: <?php echo $row['chatkick_reason']; ?>						
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unkick">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Unkick">
							</form>
							<?php } ?>
						</div>
					</div>
					
					<div class="row">
						<!-- Hibernation -->
						<div class="col w50">Hibernation:</div>
						<div class="col w50">
							<?php if($now > $row['hybernate_till']) { ?>
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="hibernate">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="text" name="reason" value="[reason]"><br>
								<input type="text" name="till" value="<?php echo $now; ?>">
								<input type="submit" value="Kick">
							</form>
							<?php } else { ?>
							Hibernated From: <?php echo Number2Date($row['hybernate_from']); ?>
							Hibernated Till: <?php echo Number2Date($row['hybernate_till']); ?>						
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unhibernate">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Unhibernate">
							</form>
							<?php } ?>
						</div>
					</div>
					
					<div class="row">
						<!-- Hibernation -->
						<div class="col w50">Ban:</div>
						<div class="col w50">
							<?php if($row['banned_date'] == 0) { ?>
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="ban">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="text" name="reason" value="[reason]"><br>
								<input type="submit" value="Ban">
							</form>
							<?php } else { ?>
							Ban Reason<?php echo $row['banned_reason']; ?><br>
							Ban Date: <?php echo Number2Date($row['banned_date']); ?><br>
							Ban By: <?php echo ShowHackerAlias($row['banned_by']); ?>
							<form method="POST" action="index.php" class="small">
								<input type="hidden" name="h" value="doadminaction">
								<input type="hidden" name="action" value="unban">
								<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
								<input type="submit" value="Unban">
							</form>
							<?php } ?>
						</div>
					</div>
					
					
				</div>
			</div>
		</div>
	</div>	
</div>
