<?php
	error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	//general settings
	date_default_timezone_set('Europe/Amsterdam');		// date timezone
	putenv("TZ=Europe/Amsterdam");

	// STUFF YOU NEED TO EDIT!!!
	$staff_clanid = 1; // if the admin creates a clan, insert that clan id here so it can be excluded from stats etc.
	$market_serverid = 4;

	$shop_servername = "1338 Software Shop";
	$shop_serverid = 2; // a server id which is owned by the admin and has FTP software on it.
	$shop_ipchange_interval = 6; // each xx hours the shop will change it's ip.
	$shop_ownerid = 1; // hacker id of the hacker owning the 1338 ingame shop.

	$fbi_servername = "FBI Central Database Server";
	$fbi_serverid = 99; // a virtual server. it's ip and password are stored in the hacker table, different for each hacker

	$gameurl					= "https://www.hackerforever.com";
	$gameemail					= "accounts@hackerforever.com";
	$smtpserver					= "mail.hackerforever.com";
	$smtpport					= 25;
	$smtpuser					= "mail@hackerforever.com";
	$smtppass					= "somepassword";
	$achievements_folder		= "images/achievements/";
	$gamepath					= "/var/www";
	$page_title = "HackerForever | The Hacking Multiplayer Online Game";
	$ibot_id = 2;        // the user id of the NPC that is the chat bot, poster of frontpage news and sender of files in missions.

	// mail stuff
	$mail_from = "no-reply@hackerforever.com";
	$mail_name = "HackerForever";

	// Password stuff
	$salt_length = 8;
	$random_password_length = 14;
	$password_key = 'ch@ozztrustsno1';
	$min_password_length = 8;
	$token_expiry = 2;	// forget password tokens expire each 48 hours

	// Bot detection stuff
	$bot_average_records = 10;	// use last 10 records to check the average
	$bot_average_threshold = 2;	// 3 seconds

	// Dupe detection stuff
	$dupe_account_creation = 15;	// 15 minutes
	$dupe_captcha_solve = 1;	// 1 minute

	// Perk stuff
	$perk_expiry = 7;	// days

	// STUFF YOU SHOULD NOT EDIT UNLESS YOU'RE REALLY SURE YOU WANT TO CHANGE THE GAME MECHANICS
	$date_format 				= "YmdHis";				// database format for dates
	$now 						= date($date_format);	// now!
	// this is for measuring loading times
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$start = $time;

	$index_refer 				= "!@#$%^&*()_+" ;		// the secret referer ;)
	$allowedcharsuser			= "letters, numbers and underscores";
	$allowedchars				= "letters, numbers and spaces";
	$hackeravatar_w				= 150;
	$hackeravatar_h				= 150;
	$clanavatar_w				= 500;
	$clanavatar_h				= 500;
	$daily_reps					= 5;					// the daily amount of reputation points you can give away
	$daily_ads 					= 2; // number of daily ads you can place.
	$daily_topics				= 15; // number of topics you can open / reply in a day
	$daily_tickets				= 2; // no more than x tickets per day
	$noob_level					= 0; 					// the lowest tool needed for an npc mission requires level $n00b_level, keep this in mind
	$noobnet_level				= 9; // as soon as you become level 10, you are automatically kicked off noobnet.
	$max_smallhack_ep_level		= 50;	// small hacks give EP until level 50
	$mission_level				= 0;	// minimum level for contracts
	$wiki_level					= 60; // the level you need to have before you can edit a wiki article
	$gateway_servercount		= 3; // the number of servers needed to keep a gateway online
	//$privatebay_level 			= 69; // max level you can have when accessing PrivateBay servers
	$moneysend_level			= 15; // the level you need to have before you can send money
	$moneysend_minimum			= 1000; // the minimum amount you can send
	$featurerequest_level		= 70; // the level you need to request features via support tickets
	$scopefree_level			= 90; // the level after wich scope restrictions are removed.
	$fbi_contract_level			= 80; // the level you need for an fbi contract
	//$scope_minchance			=	20; // minimum chance on attack or defense
	//$fixed_scope = 10; // this is an experiment. no longer do we calculate the scope, but we set a fixed scope for all
	$accounts_per_ip			= 2;					// what number of accounts per ip is allowed
	$posts_per_page				= 10;	// forum: number of posts per page before it get's paginated.	
	$allowed_avatar_extensions	= array("jpg","gif","png");	
	$hacker_avatarsize			= 75000; // 75kb
	$clan_avatarsize			= 300000; // 300kb
	$high_amount				= 999999999; // everything above this amount is monitored
	$bruteforce_limit			= 4;
	$offline_limit				= 15; // number of minutes of inactivity that have to go by to consider someone offline
	$max_hire_hackers			= 10;
	$simultanious_dl_divider	= 5; // connection efficiency / $simultanious_dl_divider = number of simultanious downloads
	$n00bnet_speed				= 3; // you have no connection, so it's set here with a variable. function GetEfficiency() uses it.
	$max_pinned					= 3; // the maximum number IMs you can pin (preventing it to get deleted)
	$max_messages				= 25; // max number of messages in your inbox and outbox.
	$max_list			    	= 5; // max number of members on your fiends/foes or ingore list
	$max_list_ignore			= 15;
	$max_recipients 			= 5; // max number of people you can mass mail
	$max_msg_length				= 90; // max length of a chat message for normal users
	$bounty_days 				= 7; // number of days a bounty stays on the bounty board
	$bounty_anonymous 			= 25000; // costs for anonymous contract
	$keep_bounty_after_ban 		= 3;	// 3 days
	$alias_available_months		= 10; // didn't play for 10 months? then your alias is up for grabs

	// premium
	$premium_time 				= 4; // months you are premium after a donation
	$premium_hacker_avatarsize	= 1000000; // 1mb
	$premium_clan_avatarsize	= 2000000; // 2mb
	$premium_max_messages		= 50; // max number of messages in your inbox and outbox.
	$premium_clan_changeinterval		= 1; // months between name changes
	$premium_hacker_changeinterval		= 6; // months between name changes
	$free_premium_days			= 7; // when you start the game you get x days free premium
	$donations_needed	= 10; // donation amount needed to pay for hosting

	// lottery
	$lottery_tickets			= 10000;				// tickets each week
	$lottery_price				= 1000;					// price of 1 lottery ticket
	$lottery_maxtickets			= 50;					// maximum tickets per hacker
	$lottery_deducted			= 0.8;					// 0.2 get deducted for costs (20%)

	// finance
	$currency 					= "$";					// money currency
	$startmoney 				= 75000;				// the money you start with
	$virusclean_costs 			= 250;					// costs of  a pc shop for repairing your infected pc, when you dont have a virusscanner
	$smallhack_jailbail 		= 1500;					// *rank = bail
	$hack_jailbail				= 2500;					// *rank = bail
	$gwhack_jailbail			= 7000;					// *rank = bail
	$hire_jailbail				= 1000;					// *rank = bail
	$hibernation_interest		= 5; 					// pay 5% interest per day while in hibernation
	$spamserver_revenue			= 750;					// hourly revenue
	$phishingserver_revenue		= 1000;					// hourly revenue
	$pornserver_revenue			= 1500;					// hourly revenue
	$filesharingserver_revenue	= 2500;					// hourly revenue

	$server_price				= 20000;				// price of server
	$server_rent				= 500;					// daily rent costs of server
	$server_refund				= 50;					// % refund
	$hacker_interest			= 5;	// % interest on hacker to hacker money transfers
	$clan_interest				= 10;	// % interest on hacker to clan and clan to hacker transfers
	$backup_fee					= 0.5;
	$gwsmall_fee				= 500; // per member per day
	$gwmedium_fee				= 1000; // per member per day
	$gwlarge_fee				= 1500; // per member per day
	$bust_jailbail				= 2000;
	$ad_price                   = 25000; // price of an ad
	$ad_hp						= 5; // price of an ad in HP
	$double_ep_amount 			= 75000000; // the amount players need to gather to trigger a double EP day

	// internet, the number of servers available to players
	$internet_cols 				= 50;		// amount of servers in a column
	$internet_rows 				= 50;		// amount of servers in a row
	$internet_size				= $internet_cols * $internet_rows;

	// intervals
	$job_interval 				= 10;		// (seconds * difficulty) +10

	$pc_hack_interval			= 20;		// time between hacks
	$fbiserver_hack_interval	= 15;	// an edit is bit faster than a normal server hack
	$server_hack_interval		= 30;		// time added to the hacktime, to create a balanced interval
	$clan_pchack_interval		= 90; 	// minutes between a gateway team hack
	$clan_serverhack_interval	= 180; 	// minutes between a gateway team hack
	$clan_gwhack_interval		= 360; 	// minutes between a gateway team hack

	$pc_hacksafe_interval		= 65;		// how much time your safe after you get hacked, 80 = default, it's deducted for higher levels with a calculation. see dopchackphp
	$gw_hacksafe_interval		= 1440; 	// hours safe after attack
	$server_hacksafe_interval	= 10;		// brute force interval
	$fbisafe_interval			= 6; 		// hours between being listed

	$server_passsuccess_interval= 20;		// wait time after succes password reset
	$server_passfailed_interval	= 2;		// wait time after failed password reset
	$server_drop_interval		= 60;		// drop date after hack
	$iprefresh_cooldown 		= 22;		// next ip refresh
	$iprefresh_cooldown_afterexecution	= 2;		// you can not refresh your ip for xx hours after executing a hack
	$iprefresh_time             = 45;       // how many minutes it takes for a refresh to get handled
	$sendim_interval			= 10;		// seconds between IMs to prevent flooding
	$captcha_min_interval		= 30;
	$captcha_max_interval		= 45;
	$initial_floodkick			= 15;		// first kick time
	$floodkick_multiplier		= 2; 		// every next floodkick the initial timer is multiplied by xx
	$isp_interval				= 12; 		// hours between ISP connection switch
	$hibernation_interval		= 21; 		// days between hibernation periods.
	$swissbank_lifetime			= 48; 		// hours for which a swissbank transfer will stay valid.
	$faultylogin_interval		= 20; 		// minutes before a locked account is freed again
	$scan_interval				= 15;		// minutes between virus scan of system
	$invite_interval		    = 60; //minutes between accepting invites
	$min_captcha_secs 			= 2; // you can not answer the captcha quicker than 2 seconds
	$max_captcha_secs			= 25; // you can not answer the captcha slower than 20 seconds
	$defaced_hours				= 48;	// your clan profile is defaced for 48 hours at least

	// timers
	$hackerforhire_jobtime 		= 10; 		// time before answer of hired hacker
	$offline_afterhack			= 90;		// xx minutes + (xx minutes/100 * (100 - security efficiency)) minimal is 25. server cooldown is 20, so after you offline someone you have 5 minutes to take it.
	$gwoffline_afterhack		= 480; // minutes offline after succesful hack [ gateway offline ]
	$mission_expire				= 150;					// xx minutes for an npc mission
	$noobmission_expire			= 20;					// xx minutes for an npc mission
	$smallhack_jailtime 		= 1.5;					// *rank = minutes
	$hack_jailtime 				= 3;					//*rank = minutes
	$gwhack_jailtime			= 5;					//*rank = minutes
	$hire_jailtime 				= 3;					// *rank = minutes
	$gateway_hacktime			= 70;		// x minutes
	$server_hacktime			= 25; // minus the internet connection efficiency
	$pc_hacktime				= 20; // minus the internet connection efficiency
	$fbi_prisontime				= 12; // hours in prison
	$internet_divider			= 8; // hacktime = standard hacktime (like stated above) - your internet efficiency divived by this number
	$internet_multiplier		= 20; // see DownloadTime() function for it';s usage. it determines speed of downloads
	$safe_multiplier 			= 5; // used in dovirus to calculate how long you are safe after an infection has been executed on your system
	$fbi_safe					= 2; // after how many hours after being listed on the fbi most wanted page do they actually start searching?
	$chat_afk					= 5; // after 5 minutes not saying anything you are considered AFK.
	$gateway_dropdays			= 3; // after a gateway is offline for 3 days due to connection problems, itś dropped.
	$banned_resetdays			= 2; // how many days after a ban your account gets reset (kick from clan, servers dropped, bank emptied)
	$cloaked_time				= 4; // hours a server will be cloaked after installing the tool on it.
	$bust_jailtime				= 2;
	$founder_inactive			= 30; // if a founder does not login for xx days, the clan is killed.
	$server_dropdays      	= 7; // not active for 7 days? then you drop your servers.
	$role_dropdays 			= 14; // x days inactive? we take your roles
	$no_revenuedays         = 2; // x days not active = no revenue for your servers
	$no_epdays				= 5; // after x days of inactivity, no ep is gained when hacking this target
	$targetfinder_inactive	= 12; // number of hours you need to be inactive before you're found by TF
	$fbi_valid_time			= 15; // number of minutes an FBI server IP or password should be at least valid
	$npc_offline_timer 		= 240;	// how many minutes the NPC gets kicked offline
	$infection_expiry		= 24;	// 24 hours
	$restore_time			= 5;	// 5 minutes
	$restore_max_time		= 180;	// 3 hours max restore time
	$delete_avatar_time		= 3; // if inactive for 3 months, kill his avatar
	// experience points
	$default_ep				= 100; // ep after a succesful hack, hacker gets $default_ep - $hack_chance = $ep2gain
	$ep_multiplier			= 3; // applies only to defaul_ep

	$npccontract_ep 			= 150;					// ep increase after successfull pc hack (NPC)
	$hackjobfailed_ep 			= 10;					// ep increase after failed pc hack
	$bailout_ep 				= 10;					// ep increase after successfull bail out
	$smallhack_ep_success		= 2;					// ep increase after succeeded small hack (* mission difficulty) + 2
	$smallhack_ep_failed		= 2;					// ep increase after failed small hack
	$virus_infected_ep			= 10;
	$virus_dropped_ep			= 15;
	$bust_ep					= 15;
	$ctf_ep						= 70;
	$noobtool_ep				= 20;
	$fbiedit_ep					= 40;
	$bust_ep					= 10;
	$npc_ep_cut					= .65;	// The EP gained is cut 65% if your target is an NPC

	// chance
	$max_job_successrate 		= 80; 					// max small hack successrate
	$smallhack_jailchance 		= 15;					// % of getting into jail after failed small hack
	$hack_jailchance 			= 25;					// % of getting into jail after failed hack
	$hire_jailchance 			= 15;					// % of getting into jail after failed hire
	$faulty_hdd_chance			= 30; 				// % chance a faulty harddrive will crash
	$fbi_ipchange_chance		= 10;
	$fbi_passwordchange_chance	= 15;
	$fbi_findip_chance			= 40;
	$privatebay_findip_chance 	= 50;
	$fbi_traceyou_chance		= 5;				// change * hours you are on the wanted list. after 10 hours the chance is % * 10
	$mst_result					= 15;				// minimal money amount stolen is xx% of total hacker cash
	$h4h_findip_chance			= 55;
	$trade_chance				= 30;			// chance of trading goods being sold every hour
	$privatebay_chance 			= 20; // chance of private bay server changing its ip
	$privatebay_numfiles  		= 25; // number of files on the ftp server
	$virusspread_chance			= 5;
	$bust_chance				= 50;
	$fbiscan_chance				= 5; // x * hours if infection they find your bfpc on their server
	$fbiinfection_minimal		= 3; // they only are able to notice the virus after x hours

	// clan stuff
	$clanhack_minsize				= 3;				// min members needed for a team hack
	$clanhack_maxsize				= 5;				// max members needed for a team hack
	$clanhack_extra_chance			= 25;

	// system degrading
	$daily_os_decrease 				= 1;
	$daily_hdd_decrease 			= 2;
	$daily_virusscanner_decrease 	= 3;
	$daily_firewall_decrease 		= 3;
	$daily_serverfirewall_decrease 	= 1;
	$daily_serversoftware_decrease 	= 1;
	$daily_server_decrease 			= 1;
	$hacksystem_decrease			= 10;

	$serverinfected_decrease		= 8; // was 3 in v6

	// database pruning
	$im_keep_after_delete		= 10; // how long keep im after deleted by both parties? for investigational purposes
	$im_keep					= 30; // how long keep unpinned messages
	$log_keep					= 30; // how long keep logs?
	$invite_keep				= 3; // how long are invites valid?
	$ticket_keep				= 10; // how long it takes before a ticket auto closes
	$topic_keep   				= 30; // how long keep old forum topics
	$fbi_log_keep 				= 7; // how long to keep the fbi logs
	$chat_keep					= 5; // how long keep chat logs

	// leveling system
	//$firstlevel_ep 				= 10;	// how much ep you need to reach level 1
	//$level_interval_increase 	= 1.055; //updated on 25-07-2016, it was 1.07042; // level 2 is 10 * 1.07042, level 3 - level 2 + (level 2 - level 1) * 1.07042, etc
	$firstlevel_ep 				= 100;	// how much ep you need to reach level 1 (updated on 20160216)
	$level_interval_increase 	= 1.0361; // slight increase of level interval (updated on 20160216)
	$convention_price			= 5000;	// level 1 pays 5000 for a convention, level 2 pays level1 * 1.1, level 3 pays level2 * 1.1 etc..
	$maxlevel 					= 175;	// you can not reach any higher level then $maxlevel
	$maxskill					= 1750; // max level 100, every 5 levels you can earn 50 skill points. 100/5 = 20 * 50 = 1000
	$maxsystem					= 1000; // 3x software max 166 + 3x hardware max 166 = 1000
	$maxserver					= 200; // firewall + efficiency = 200
	$skillslots_per_convention	= 50;

	// hackpoints
	$end_contract_hp = 2;
	$hpc2contract_time = 15;	// 1 hp = 15 minutes

	// Did you know?
	$dyk_chance	= 10;
	$dyk_display = 10;
	$dyk_noobnet_chance = 50;

	// Changelog
	$changelog_types = array(1 => "<span class='red'>Bug Fix</span>", 2 => "<span class='green'>Feature</span>", 3 => "<span style='color:#F88017;'>Tweak</span>");
	$display_logs = 10;

	// BattleSys
	$level_percentage	= 150; // more then 100% because LEVEL is most important
	$skill_percentage	= 100;
	$system_percentage 	= 100;
	$server_percentage 	= 100;
	$default_pvp_chance	= 30; // % chance
	$default_pvs_chance	= 25; // % chance
	$point_multiplier	= 2;
	$ethicpoints		= 5;	// extra points (* multiplier) in battlesys for certain ethics (see GetEthicPoints)

	// CTF
	$ctf_mintime	= 30;	
	$ctf_maxtime	= 150; // # minutes to decrypt
	$ctf_fileid		= 83; // product_id of the flag file
	$ctf_price		= 15000; // price
	$ctf_serverid	= 3; // id of ftp server holding the file
	$ctf_ownerid	= 1;
	$ctf_reward_min	= 75000;
	$ctf_reward_max	= 100000;
	$ctf_name		= "EncryptedBankFile.ctf";

	// Overclock
	$overclock_increase_chance = 10;
	$overclock_increase_time_pc = 60;	// 1 hour
	$overclock_increase_time_server = 120;	// 2 hours
	$overclock_next_overclock_hours = 24;	// you can perform another overclock after x hours

	// King of the ring
	/*$kotr = array();
	$kotr['takeback_chance'] = 10;
	$kotr['internet_col'] = 5;
	$kotr['internet_rows'] = 5;
	$kotr['initial_serverid'] = 5001;
	$kotr['tier3_servers'] = array(5001, 5002, 5003, 5004, 5005, 5006, 5010, 5011, 5015, 5016, 5020, 5021, 5022, 5023, 5024, 5025);
	$kotr['tier2_servers'] = array(5007, 5008, 5009, 5012, 5014, 5017, 5018, 5019);
	$kotr['tier1_servers'] = array(5013);
	$kotr['all_servers'] = array_merge ($kotr['tier3_servers'], $kotr['tier2_servers'], $kotr['tier1_servers']);
	$kotr['reward'] = array("EP" => 250, "Cash" => 100000);
	$kotr['tier1_npc'] = 34146;
	$kotr['tier2_npc'] = 34145;
	$kotr['tier3_npc'] = 34144;
	$kotr['points'] = array("Tier3" => 2, "Tier2" => 5, "Tier1" => 20);
	$kotr['min_tier_servers'] = 2;
	$kotr['min_win_points'] = 34;	// 2 outer servers, 2 inner servers and the mid server
	$kotr['clan_id'] = 100;
	$kotr['all_npcs'] = array(34145, 34144, 34146);*/

	// War
	$war = array();
	$war['points'] = 500; // initial value of points
	$war['lock_percentage'] = 0.5; // percentage of cash to be locked
	$war['pc_points'] = 5;	// points you deduct from your enemy for hacking their pc
	$war['server_points'] = 10;
	$war['pc_ddos_points'] = 20;
	$war['server_ddos_points'] = 40;
	$war['gw_ddos_points'] = 50;
	$war['surrender_percentage'] = 0.30;	// percentage of money lost when you surrender
	$war['min_clan_age'] = 7; // days a clan must exit before starting a war
	$war['ep_reward'] = 500; // reward for winning a war
	$war['cooldown_days'] = 7; // a xx day cooldown after wars
	$war['inactive_days'] = 2;

	// referer rewards
	$referral_reward_ep = 250;
	$referral_reward_cash = 1000000;
	$referral_level = 50;

	// Fill an array with Product Title => ID for ease-of-use
	$PRODUCT = array();
	$result = mysqli_query($link, "SELECT title, id FROM product ORDER BY id DESC");
	while($row = mysqli_fetch_assoc($result))
	$PRODUCT[$row['title']] = $row['id'];

	// THIS IS THE MOST IMPORTANT QUERY IN THE GAME. IT FILLS A RECORDSET WITH THE DATA OF THE CURRENT LOGGED IN HACKER
	if (isset($_SESSION['hacker_id'])) 
	{
		$result = mysqli_query($link, "SELECT hacker.*, clan.lastforum_date, clan.alias as clan_alias, bankaccount_password FROM hacker LEFT JOIN clan ON hacker.clan_id = clan.id WHERE hacker.id = ".$_SESSION['hacker_id']);
		if (mysqli_num_rows($result) == 0 ) include ("./pages/logout.php");
		else 
		{
			$hackerdata = mysqli_fetch_assoc ($result);
			$_SESSION['nextserverhack_date'] = $hackerdata['nextserverhack_date'];
			$_SESSION['nextpchack_date'] = $hackerdata['nextpchack_date'];
			$_SESSION['nextjob_date'] = $hackerdata['nextjob_date'];
			$_SESSION['nextnpc_date'] = $hackerdata['nextnpc_date'];
			$_SESSION['scan_till'] = $hackerdata['scan_till'];
			$_SESSION['offline_till'] = $hackerdata['offline_till'];
			$_SESSION['jailed_till'] = $hackerdata['jailed_till'];
			$_SESSION['prison_till'] = $hackerdata['prison_till'];
			$hackerdata_id = $hackerdata['id']; // used in global statement of functions.php
		}	
	}
?>