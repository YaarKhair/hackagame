<section id="sidebar" class="col w30">

	<!-- SIDEBAR SECTION 1 -->
	<div class="row hr">
		<div class="col w35" id="player-avatar">
			<?php echo ShowAvatar($hackerdata['id'], 0, '', "hacker", true); ?>
		</div>
		<div class="col w65">
		<?php echo ShowHackerAlias($hackerdata['id'], 0, 0, true, true, 0, 0, 0); ?>
			<div class="row mv10">
				<div class="col w50"><a href="#" title="<?php echo Date("F j, Y"); ?>"><img class="icon" src="theme/icons/clock.png" alt="Icon"> <span id="servertime"></span></a></div>
				<div class="col w50"><a title="Level Progress" href="?h=personalstats"><img class="icon" src="theme/icons/level.png" alt="Icon"> <?php echo EP2Level($hackerdata['ep']); ?><small> (<?php echo EP2LevelProgress($hackerdata['id']); ?>%) </small></a></div>
			</div>
			<div class="row mv10">
				<div class="col w50"><a title="Experience Points" href="?h=history&type=ep"><img class="icon" src="theme/icons/binary.png" alt="Icon"> <?php echo number_format($hackerdata['ep']); ?></a></div>
				<div class="col w50"><a title="Cash" href="?h=dobankaccount&account=hacker"><img class="icon" src="theme/icons/dollar.png" alt="Icon"> $<?php echo number_format($hackerdata['bankaccount']); ?></a></div>
			</div>
		</div>
	</div>

	<?php if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2) || InGroup($hackerdata['id'], 6)) { 
		// Get new tickets, closed and open tickets number
		$tickets = array("New" => 1, "Open" => 2, "Closed" => 3);
		$tickets_num = array();
		foreach($tickets as $type => $ticket_id) {
			$num = mysqli_get_value_from_query("SELECT count(ticket.id) as number FROM ticket LEFT JOIN ticket_status ON ticket.status_id = ticket_status.id WHERE respons_id = 0 AND status = $ticket_id", "number");
			$tickets_num[$type] = $num;
		}
	
		$num_wiki = mysqli_get_value_from_query("SELECT count(id) as num FROM wiki WHERE pending = 1", "num");	
	
	?>
	<!-- SIDEBAR ADMIN PANEL -->
	<?php if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2) || InGroup($hackerdata['id'], 6)) { ?> 
	<div class="accordion hr"  id="admin-panel">
		<input class="accordion-toggle" type="checkbox" id="ac-admin" checked="false">
		<label for="ac-admin" onclick="saveAccordionStatus('ac-admin')">Administrator Menu</label>
		<div class="row accordion-box">
			<div class="col w50">
				<a href="?h=modtickets&status_id=1">Tickets, New (<?php echo $tickets_num['New']; ?>)</a>
				<a href="?h=modtickets&status_id=2">Tickets, Open (<?php echo $tickets_num['Open']; ?>)</a>
				<a href="?h=modtickets&status_id=3">Tickets, Closed (<?php echo $tickets_num['Closed']; ?>)</a>
				<?php if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) { ?>
				<a href="?h=modmodnotes">Modnotes</a>
				<a href="?h=modlog">Modlog</a>
				<a href="?h=moddyk">Tooltip Management</a>
				<a href="?h=modbattlesys">Battlesys</a>
				<a href="?h=internet&version=2">NPC Internet</a>
				<a href="?h=modgroups">User Groups</a>
				<a href="?h=modactivity">Active Hackers</a>
				<a href="?h=modstaffmessage">Staff Message</a>
				<a href="?h=moddoubleep">Double EP</a>
				<?php } ?>
			</div>
			<div class="col w50">
			<?php if(InGroup($hackerdata['id'], 1) || InGroup($hackerdata['id'], 2)) { ?>
				<a href="?h=modwiki">Moderate Wiki (<?php echo $num_wiki; ?>)</a>
				<a href="?h=modchathistory">Chat History</a>
				<a href="?h=modmoney">Money Tracker</a>
				<a href="?h=moddupes">Possible Duplicates</a>
				<a href="?h=modbots">Possible Botters</a>
				<a href="?h=modreferals">Refered Hackers</a>
				<a href="?h=modeplist">EP Gained List</a>
				<a href="?h=modtutorial">Tutorial Management</a>
				<a href="?h=modbanlist">Bans</a>
				<a href="?h=modlistnew">Signups</a>
				<a href="?h=internet&gateways=1">Show All Gateways</a>
				<a href="?h=moddonations">Donations</a>
			<?php } ?>
			</div>
		</div>
	</div>
	<?php } ?>
	<?php } ?>

	<!-- SIDEBAR SECTION 2 -->
	<div class="hr">
		<h3 class="mv10">System status</h3>
		<div class="row mv10">
			<div class="col w40"><a href="?h=hackapc" title="PC Hack"><img class="icon" src="theme/icons/pc.png" alt="Icon"> <span id="pcount">0</span></a></div>
			<div class="col w35"><a href="?h=joblist" title="Small Hack Job"><img class="icon" src="theme/icons/job.png" alt="Icon"> <span id="jcount">0</span></a></div>
			<div class="col w25"><a href="?h=mailbox&folder=inbox" title="Messages"><img class="icon" src="theme/icons/mail.png" alt="Icon"> <span id="inboxcount"></span></a></div>
		</div>
		<div class="row mv10">
			<div class="col w40"><a href="?h=hackaserver" title="Server Hack"><img class="icon" src="theme/icons/server.png" alt="Icon"> <span id="scount">0</span></a></div>
			<div class="col w35"><a href="?h=antivirusonline" title="Antivirus Scan"><img class="icon" src="theme/icons/antivirus.png" alt="Icon"> <span id="acount">0</span></a></div>
			<div class="col w25"><a href="?h=system" title="Firewall Status"><img class="icon" src="theme/icons/firewall.png" alt="Icon"> <?php echo GetProgress($hackerdata['id'], "FIREWALL", true)?></a></div>
		</div>
		<div class="row mv10">
			<div class="col w40"><a href="?h=npcmission" title="Contract Job"><img class="icon" src="theme/icons/contract.png" alt="Icon"> <span id="mcount">0</span></a></div>
			<div class="col w35"><a href="?h=transfers" title="Downloads"><img class="icon" src="theme/icons/download.png" alt="Icon"> <span id="dcount">0</span></a></div>
			<div class="col w25"><a href="?h=history&type=hp" title="Hack Points"><img class="icon" src="theme/icons/hackpoints.png" alt="Icon"> <?php echo $hackerdata['hackpoints_credit']; ?></a></div>
		</div>
	</div>

	<!-- SIDEBAR SECTION 3 -->
	<?php
	$num_success = mysqli_get_value_from_query("SELECT count(id) as num FROM infection WHERE ready = 1 AND success = 1 AND spreading = 0 AND hacker_id = {$hackerdata['id']}", "num"); // viruses ready for execution
	$num_pending = mysqli_get_value_from_query("SELECT count(id) as num FROM infection WHERE ready = 0 AND hacker_id = {$hackerdata['id']}", "num"); // viruses not yet ready for execution
	$num_chat = mysqli_get_value_from_query("SELECT count(id) as num FROM hacker WHERE invisible = 0 AND onchatpage_date > ".date($date_format, strtotime("-20 seconds")), 'num');
	?>
	<div id="actions" class="hr">
		<div class="row">
			<div class="col w50">
				<h3 class="mv10">Toolbox</h3>
				<a href="?h=system">System Manager</a>
				<a href="?h=servermanager">Server Manager (<?php echo NumServers($hackerdata['id']); ?>)</a>
				<a href="?h=logs">Logs</a>
				<a href="?h=software" class="tooltips">Browse HDD<?php if($hackerdata['id'] == 8157 || $hackerdata['id'] == 1) { echo "<span>This allows you to view you software you have downloaded and allows you to manage it</span>"; }?></a>
				<a href="?h=dorefreship" onclick="return confirm('Are you sure you want to refresh your IP?');">Refresh IP</a>
				<a href="?h=notepad">Notepad</a>
				<a href="?h=toolbox">Ping Tools</a>
			</div>
			<div class="col w50">
				<h3 class="mv10">Hacking</h3>
				<a href="?h=infections">Infections (<?php echo $num_pending.'/'.$num_success; ?>)</a>
				<a href="?h=joblist">Small Hacks</a>
				<a href="?h=hackapc">Hack a PC</a>
				<a href="?h=hackaserver">Hack a Server</a>
				<a href="?h=fbiloginpage">Hack FBI DB</a>
				<a href="?h=npcmission">Contract Hacks</a>
				<a href="?h=clanhackgw">DDoS A Gateway</a>
				<a href="?h=clanhackserver">DDoS A Server</a>
				<a href="?h=clanhackpc">DDoS A PC</a>
			</div>
		</div>
		<div class="row">
			<div class="col w50">
				<h3 class="mv10">Internet</h3>
				<!-- <a href="?h=internet">Internet Map</a> //-->
				<a href="?h=ftplist">Public FTPs</a>
				<a href="?h=chat">Public Chatroom (<?php echo '<span style="color:red">'.$num_chat.'</span>'; ?>)</a>
			</div>
			<div class="col w50">
				<h3 class="mv10">Services</h3>
				<a href="?h=hacker4hire">Hacker4Hire</a>
				<a href="?h=antivirusonline">Antivirus Online</a>
				<a href="?h=backuponline">Backup Online</a>
			</div>
		</div>
	</div>
	
	<!--<div id="translate" class="hr">
		<div class="row">
			<div id="google_translate_element"></div><script type="text/javascript">
			function googleTranslateElementInit() {
			  new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL}, 'google_translate_element');
			}
			</script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
		</div>
	</div> -->
	
	<!-- SIDEBAR SECTION 4 -->
	<div id="social" class="center">
		<a target="_blank" href="http://www.facebook.com/HackerForever"><img src="theme/images/tile-facebook.png" title="Facebook"></a>
		<a target="_blank" href="http://www.twitter.com/#!/hackthegame"><img src="theme/images/tile-twitter.png" title="Twitter"></a>
		<a target="_blank" href="?h=referral"><img src="theme/images/tile-mail.png" title="Invite a Friend!"></a>
		<a target="_blank" href="?h=premiumhacker"><img src="theme/images/tile-paypal.png" title="Donate via PayPal or SMS"></a>
		<a href="?h=logout"><img src="theme/images/tile-logout.png" title="Logout"></a>
	</div>

</section>