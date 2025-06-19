<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
	// refresh ip
	if ($hackerdata['iprefresh_date'] > 0) $refreshtext = '&nbsp;[IP Refresh pending at '.Number2Date($hackerdata['iprefresh_date']).']';
    elseif ($hackerdata['nextiprefresh_date'] >  $now) $refreshtext = '&nbsp;[Service available at '.Number2Date($hackerdata['nextiprefresh_date']).']';
	else {
		$price = mysqli_get_value ("price", "product", "id", 18); // ip refresher
		$refreshtext= "&nbsp;[<a hr-lightef=\"index.php?h=dorefreship\">Request a new IP</a> ($currency $price)]";
	}	
?>
	<div class="row mv10">
		<div class="col w45">			
			<h2>System Specs</h2>
			<div class="row hr-light dark-bg">
				<div class="col w40">CPU</div>
				<div class="col w60"><?php if (!HasInstalled($hackerdata['id'], "CPU")) echo "286"; else echo HasInstalled($hackerdata['id'], "CPU"); ?></div>
			</div>	
			<div class="row hr-light dark-bg">
				<div class="col w40">Mainboard</div>
				<div class="col w60"><?php if (!HasInstalled($hackerdata['id'], "MAINBOARD")) echo "8086"; else echo HasInstalled($hackerdata['id'], "MAINBOARD"); ?></div>
			</div>	
			<div class="row hr-light dark-bg">
				<div class="col w40">Memory</div>
				<div class="col w60"><?php if (!HasInstalled($hackerdata['id'], "MEMORY")) echo "4mb"; else echo HasInstalled($hackerdata['id'], "MEMORY"); ?></div>
			</div>
		</div>
		
		<div class="col w10">&nbsp;</div>
		
		<div class="col w45">
			<h2>Harddisk details</h2>
			<div class="row hr-light dark-bg">
				<div class="col w40">Type</div>
				<div class="col w60"><?php 
					if (!HasInstalled($hackerdata['id'], "HDD")) echo "defect"; 
					else {
						echo HasInstalled($hackerdata['id'], "HDD");
					}	
				?></div>
			</div>
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Health</div>
				<div class="col w60"><?php 
					if (!HasInstalled($hackerdata['id'], "HDD")) echo "defect"; 
					else {
						echo ShowProgress(GetEfficiency($hackerdata['id'], "HDD"), GetInitialEfficiency($hackerdata['id'], "HDD"));
					}	
				?></div>
			</div>	
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Size</div>
				<div class="col w60"><?php if (!HasInstalled($hackerdata['id'], "HDD")) echo "defect"; else echo DisplaySize(HDDsize($hackerdata['id'])); ?></div>
			</div>	
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Space Used</div>
				<div class="col w60"><?php if (!HasInstalled($hackerdata['id'], "HDD")) echo "defect"; else echo DisplaySize(HDDuse($hackerdata['id'])); ?></div>
			</div>	
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Space Free</div>
				<div class="col w60"><?php if (!HasInstalled($hackerdata['id'], "HDD")) echo "defect"; else echo DisplaySize((HDDsize($hackerdata['id']) - HDDuse($hackerdata['id']))); ?><br><?php if (!HasInstalled($hackerdata['id'], "HDD")) echo "defect"; else echo ShowProgress((HDDsize($hackerdata['id']) - HDDuse($hackerdata['id'])), HDDsize($hackerdata['id'])); ?></div>
			</div>
		</div>
	</div>
	
	<div class="row mv10">
		<div class="col w45">			
			<h2>Installed Software</h2>
			<div class="row hr-light dark-bg">
				<div class="col w40">Operating System</div>
				<div class="col w60"><?php 
					if (!HasInstalled($hackerdata['id'], "OS")) echo "DOS"; 
					else echo HasInstalled($hackerdata['id'], "OS");
				?></div>
			</div>
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Logs</div>
				<div class="col w60"><?php if (!HasInstalled($hackerdata['id'], "ACCESSLOGS")) echo "--not installed"; else echo HasInstalled($hackerdata['id'], "ACCESSLOGS"); ?></div>
			</div>
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Security Pack</div>
				<div class="col w60"><?php 
					if (!HasInstalled($hackerdata['id'], "SECURITY")) echo "--not installed"; 
					else {
						echo HasInstalled($hackerdata['id'], "SECURITY");
						echo '<br>';
						echo ShowProgress(GetEfficiency($hackerdata['id'], "SECURITY"), GetInitialEfficiency($hackerdata['id'], "SECURITY"));
					}	
				?></div>
			</div>
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Firewall</div>
				<div class="col w60"><?php 
					if (!HasInstalled($hackerdata['id'], "FIREWALL")) echo "--not installed"; 
					else {
						echo HasInstalled($hackerdata['id'], "FIREWALL");
						echo '<br>';
						echo ShowProgress(GetEfficiency($hackerdata['id'], "FIREWALL"), GetInitialEfficiency($hackerdata['id'], "FIREWALL"));
					}	
				?></div>
			</div>
			
			<div class="row hr-light dark-bg">
				<div class="col w40">&nbsp;</div>
				<div class="col w60">&nbsp;</div>
			</div>
		</div>
		
		<div class="col w10">&nbsp;</div>
		
		<div class="col w45">
			<h2>Network</h2>
			<div class="row hr-light dark-bg">
				<div class="col w40">Connected to</div>
				<div class="col w60"><?php if (mysqli_get_value("network_id", "hacker", "id", $hackerdata['id']) != 2) echo "n00bNET"; else echo "Internet"; ?></div>
			</div>	
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Connection type</div>
				<div class="col w60"><?php if (!HasInstalled($hackerdata['id'], "INTERNET")) echo "Dial-up"; else echo HasInstalled($hackerdata['id'], "INTERNET"); ?></div>
			</div>
			
			<div class="row hr-light dark-bg">
				<div class="col w40">IP Address</div>
				<div class="col w60"><?php echo $hackerdata['ip']; ?></div>
			</div>
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Gateway IP</div>
				<div class="col w60"><?php echo GetGatewayIP($hackerdata['clan_id']); ?></div>
			</div>
			
			<div class="row hr-light dark-bg">
				<div class="col w40">Gateway Status</div>
				<div class="col w60"><?php
						$gateway_id = GetGateway($hackerdata['clan_id']);
						if (IsOnlineServer($gateway_id)) echo '<span style="color:green">Online</span>'; 
						else {
							echo '<span style="color:red">Offline</span>';
							//if (IsFounder($hackerdata['id'])) {
								$online_date = mysqli_get_value ("offline_till", "server", "id", $gateway_id);
								echo '<br>[Back online @'.Number2Date($online_date).']';
							//}	
						}	
					?>
				</div>
			</div>	
		</div>
	</div>