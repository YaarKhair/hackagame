<?php
	// this function is used for
	// captcha:  if mobile you have a longer solving period 
	// sessions: if mobile we do not compare IPs
	function detect_mobile() 
	{
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|playbook|sagem|sharp|sie-|silk|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER['HTTP_USER_AGENT']))
			return true;
		else
			return false;
	}
	function is_mobile($input) {
	    if(preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|playbook|sagem|sharp|sie-|silk|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $input))
	        return true;
	    else
	        return false;
	}

	session_start();
	// modules
	include_once("modules/connectdb.php");
	include_once("modules/settings.php");
	include_once("modules/functions.php");
	
	// page to load
	$page2load = "main"; // default page
	if (!empty($_REQUEST['h'])) $page2load = $_REQUEST['h'];
	$page2load = sql($page2load); // sanitize the input
	
	// are you visiting the index.php file without being logged in?
	if (!isset($_SESSION['hacker_id'])) {
		header('location: guest.php');
		die();
	}

	// php errors? only display them for devs
	if (InGroup($hackerdata['id'], 5) || $hackerdata['id'] == 55362) ini_set('display_errors', 1); 

	// you are logged in, lets check sum stuff
	if (!detect_mobile() && $_SESSION['sesdat01'] != sha1(GetUserIP()) && $_SESSION['hijacking'] == true) $page2load = "logout"; // if you are not mobile, we check the ip stored in the session with your current ip
	if (($_SESSION['sesdat02'] != sha1($hackerdata['password']) || $_SESSION['sesdat03'] != sha1($hackerdata['alias'])) && $_SESSION['hijacking'] == true) $page2load = "logout";

	// adblocker? let's block you.
	//if ($hackerdata['id'] ==1 && $hackerdata['adblock'] >  1 && $hackerdata['donator_till'] != 7) $page2load = "block";

	// allowed pages when restricted
	$allowed_jail = Array("premiumhacker", "dojail", "logout", "games", "playgame", "forum" , "doforum", "chat", "captcha", "docaptcha", "tickets", "newticket", "doreadticket", "doticket", "wiki", "modtickets", "adfly"); //mod tickets for normal mods who can be jailed
	$allowed_banned = Array("logout", "tickets", "newticket", "doreadticket", "doticket", "banned", "doedithacker");
	$allowed_prison = Array("logout", "chat");
	$allowed_forcepasschange = array("login", "logout", "doedithacker");

	// banned while playing? let's log you out then
	if ($hackerdata['banned_date'] > 0 && !in_array($page2load, $allowed_banned)) $page2load = "logout"; 

	// captcha time? then go captcha page until you type it correctly
	if ($hackerdata['nextcaptcha_date'] < $now && $page2load != "logout" && $page2load != "forum" && $page2load != "captcha" && $page2load != "welcome" && substr($page2load, 0, 2) != "do" && $hackerdata['banned_date'] == 0) {
		if (isset($_SERVER['QUERY_STRING'])) $_SESSION['redir'] = $_SERVER['QUERY_STRING'];
		else $_SESSION['redir'] = '';
		$page2load = "captcha";
		//if ($hackerdata['show_ads'] == 1) $page2load = "adfly"; // remove this line when you're not using adfly anymore
	}

	// You got a few minutes on your restore timer? then let's start your restoration status
	if($hackerdata['restoring_minutes'] > 0) {

		// Your restore time is larger than the max_restore_time? 
		$restore_minutes = $hackerdata['restoring_minutes'];
		if($hackerdata['restoring_minutes'] > $restore_max_time) $restore_minutes = $restore_max_time;

		// was there a protection timer set? then move that timer to the end of your new offline time.
		if ($hackerdata['unhackable_till'] > $now) {
			$unhackable_minutes = round(SecondsDiff ($now, $hackerdata['unhackable_till']) / 60);
			$unhackable_minutes += $restore_minutes;
			$unhackable_till = date($date_format, strtotime("+".$unhackable_minutes." minutes"));
		}
		else $unhackable_till = $hackerdata['unhackable_till']; // no change

		// if the player was already offline, add the remaining offline time to the restore time
		if ($hackerdata['offline_till'] > $now) {
			$offline_minutes = round(SecondsDiff ($now, $hackerdata['offline_till']) / 60);
			$restore_minutes += $offline_minutes;
		}
		$restore_till = date($date_format, strtotime("+".$restore_minutes." minutes"));


		// Send him offline, set his restoring minutes to 0 and offline from to now, add a log in his system.
		$restore_result = mysqli_query($link, "UPDATE hacker SET unhackable_till = '$unhackable_till', offline_till = '$restore_till', restoring_minutes = 0, offline_from = '$now' WHERE id = {$hackerdata['id']}");
		AddLog($hackerdata['id'], 'hacker', 'system', 'TCP/IP: You have lost connection to the internet for '.$restore_time.' minutes as your system restores', $now);

		$page2load = "offline";
	}

	if ($page2load != "captcha" && $page2load != "docaptcha" && $page2load != "adfly") $_SESSION['redir'] = "main";

	// Force change pass?
	if($hackerdata['force_passchange'] == 1 && !in_array($page2load, $allowed_forcepasschange) && !isset($_SESSION['immitator_id'])) { $page2load = "forcepasswordchange"; $_SESSION['loaded'] = 1; }
	// offline? then stare at the offline page until we say you had enough!
	if ($now >= $hackerdata['offline_from'] && $now <= $hackerdata['offline_till'] && $page2load != "logout") $page2load = "offline";
	// jailed? then go to jail and wait :)
	if ($hackerdata['jailed_till'] > $now && !in_array($page2load, $allowed_jail)) $page2load = "jail";
	// prison? then go to prison and wait :)
	if ($hackerdata['prison_till'] > $now &&  !in_array($page2load, $allowed_prison)) $page2load = "prison";
	// going into hibernation while playing? lets log you out.
	if ($hackerdata['hybernate_till'] > $now) $page2load = "logout";
	// not yet selected an ethic?
	if ($hackerdata['ethic_id'] == 0 && ($page2load != "doselectethic" && $page2load != "logout" && $page2load != "activate" && $page2load != "forcepasswordchange" && $page2load != "doedithacker" && $hackerdata['banned_date'] == 0)) $page2load = "selectethic";
	// not yet activated? warn them! THIS LINE LAST, SO YOU ALWAYS GET THIS IF NOT ACTIVATED
	if ($hackerdata['activationcode'] != "" || $hackerdata['activationcode'] != NULL && $page2load != "activate") $page2load = "activationwarning";
		
	if ($hackerdata['show_ads'] == 1 && $page2load == "adfly") {	
		// adfly link to captcha
		header("Location: http://adf.ly/OxOvZ");
		exit;
	}
	
	// find last finishing download or upload for counter
	$result2 = mysqli_query($link, "SELECT ready_date FROM filetransfer WHERE (destination_entity = 'hacker' AND destination_id = {$hackerdata['id']}) OR (source_entity = 'hacker' AND source_id = {$hackerdata['id']}) ORDER BY ready_date DESC LIMIT 1");
	if (mysqli_num_rows($result2) > 0) { $row2 = mysqli_fetch_assoc($result2); $dcount = $row2['ready_date']; }
	else $dcount = 0;

?>
<!DOCTYPE html>
<html>
    
    <head>
<?php
		$page_title .= " | $page2load";
		echo "<title>$page_title</title>";
?>	
        <meta charset="UTF-8">
		
		<!-- Cache -->
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
		
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="theme/images/favicon.png">
        <link rel="stylesheet" href="theme/style.css?<?php echo time(); ?>" type="text/css">
        <?php if($hackerdata['id'] == 8157 || $hackerdata['id'] == 1) { echo '<link rel="stylesheet" href="theme/tooltip.css?'.time().'">'; } ?>
        <!-- General meta tags -->
        <meta name="description" content="The online hacking multiplayer browser game. Join now, it's free!">
        <!-- Facebook meta tags -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="HackerForever">
        <meta property="og:url" content="<?php echo $gameurl; ?>">
        <meta property="og:description" content="The online hacking multiplayer browser game. Join now, it's free!">
        <meta property="og:image" content="<?php echo $gameurl; ?>/theme/images/favicon.png">
        <!-- Twitter meta tags -->
        <meta property="twitter:card" content="summary">
        <meta property="twitter:title" content="HackerForever">
        <meta property="twitter:url" content="<?php echo $gameurl; ?>">
        <meta property="twitter:description" content="The online hacking multiplayer browser game. Join now, it's free!">
        <meta property="twitter:image" content="<?php echo $gameurl; ?>/theme/images/favicon.png">
			
<?php	if ($hackerdata['show_ads'] == 1 || $hackerdata['donator_till'] < $now) { ?>
		<!-- mobile ads code //-->
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<script>
		  (adsbygoogle = window.adsbygoogle || []).push({
			google_ad_client: "ca-pub-1898727762837373",
			enable_page_level_ads: true
		  });
		</script>
<?php	} ?>
		<style type="text/css">
			.notblocked {
				display: none;
			}
			.blocked {
				display: block;
			}
		</style>		
    </head>
    
    <body>
		<div id="sound_element" style="display:none;"></div>
        
        <!-- HEADER BEGIN -->
        <header>
            <div class="container">
				<?php include ("pages/header.php"); ?>
                <!-- NAV BEGIN -->
				<?php include ("pages/menu.php"); ?>
                <!-- NAV END -->
            </div>
        </header>
        <!-- HEADER END -->
        <!-- MAIN PAGE BEGIN -->
        <section id="main-wrapper">
            <div id="main" class="container row">
                <!-- SIDEBAR BEGIN -->
				<?php include ("pages/sidebar.php"); ?>
                <!-- SIDEBAR END -->
                <!-- MAIN CONTENT BEGIN -->
                <section id="content" class="col w70">
					<p id="adonationwouldbenice" class="notblocked message error"><span style="font-size: 20px !important;">Please consider whitelisting our website in your ad blocker. Or make a donation and disable ads ingame. Ads keep this game FREE.</span></p>					
					<?php
					// staff messages
					$result2 = mysqli_query($link, "SELECT hacker_id, message FROM ad WHERE date = '0'");
					if (mysqli_num_rows($result2) > 0) {
						$row2 = mysqli_fetch_assoc($result2);
						PrintMessage ("error", "<strong>".ShowHackerAlias($row2['hacker_id'])."</strong> - {$row2['message']}");
					}    
					// Ads
					$adtime = substr($now, 0, 10);
					$result2 = mysqli_query($link, "SELECT hacker_id, message FROM ad WHERE date = '$adtime'");
					if (mysqli_num_rows($result2) > 0) {
						$row2 = mysqli_fetch_assoc($result2);
						PrintMessage ("info", "<strong>".ShowHackerAlias($row2['hacker_id'])."</strong> - {$row2['message']}");
					}    
					?>
					<?php
						if(is2xEP()) PrintMessage("Locked", "Today is double EP day!");
					?>
					<?php if(isset($_SESSION['immitator_id'])) echo "<div class='row'><form action='index.php' method='POST'><input type='hidden' name='h' value='doadminimmitate'><input type='hidden' name='action' value='endimmitate'><input type='submit' value='End Immitate'></form></div>"; ?>
                    <div id="<?php echo $page2load; ?>">
					<?php
						// set active page.
						if ($hackerdata['id'] > 0) {
							if ($hackerdata['invisible'] == 0 && !isset($_SESSION['immitator_id'])) $result = mysqli_query($link, "UPDATE hacker SET last_click = '".$now."', current_page = '".$page2load."' WHERE id = ".$hackerdata['id']);
							if ($hackerdata['network_id'] == 1) {
								$result = mysqli_query($link, "SELECT id FROM im WHERE reciever_id = {$hackerdata['id']} AND sender_id = $ibot_id AND unread = 1");
								if (mysqli_num_rows($result) > 0) PrintMessage ("info", "You have an important message in your Webmail!");
							}
						}

						$fullpage2load = "./pages/".$page2load.".php";
						$return_code = 1;
						if (file_exists($fullpage2load)) 
							$return_code = include($fullpage2load);
						else 
							include("./pages/404.php");
						if ($return_code != 1) {
							PrintMessage ("Error", $return_code);
							$result = mysqli_query($link, "UPDATE hacker SET current_page = 'error' WHERE id = ".$hackerdata['id']); // set currentpage as error, so clanhacks fail if an error occurs, etc
						}	
					?>
                    </div>
                    
                </section>
                <!-- MAIN CONTENT END -->
                
            </div>
            
        </section>
        <!-- MAIN PAGE END -->
<?php	if ($hackerdata['show_ads'] == 1 || $hackerdata['donator_till'] < $now) { ?>
	
        <script type="text/javascript">
			var adblock = true;
			</script>
			<script type="text/javascript" src="adframe.js"></script>
			<script type="text/javascript">
			if(adblock) {
				var d = document.getElementById("adonationwouldbenice");
				d.className = "blocked message error";
			}
		</script>
<?php	}	?>		
		<script type="text/javascript">
		<?php 
			include ("js/settings.js"); 
			include ("js/final.js"); 
			if ($page2load == "chat") {
				include('js/chat.js');
			}
			if ($page2load == "captcha") {
				include('js/captcha.js');
			}
			//if ($hackerdata['show_ads'] == 1 && $hackerdata['donator_till'] < $now) include ("js/gglblock.js"); // if your profile is set to display ads and you block them, we alert you
		?>
		</script>
		
        <!-- ADS BEGIN -->
        <section id="ads" class="container" style="text-align: center;">
			<?php if ($hackerdata['show_ads'] == 1)  { ?>
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<!-- HackerForever -->
			<ins class="adsbygoogle"
				 style="display:inline-block;width:728px;height:90px"
				 data-ad-client="ca-pub-1898727762837373"
				 data-ad-slot="1132754641"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
			<?php } ?>
		</section>
        <!-- ADS END -->
        
        <!-- FOOTER BEGIN -->
        <footer>
            <div class="container row">
				<?php include ("pages/footer.php"); ?>
            </div>
        </footer>
        <!-- FOOTER END -->
		<!--- SLIDE IN BEGIN -->
		<?php
			$show_ctf = false;
			if(ShowFileHolder() != "CTF FTP") $show_ctf = true;
			
			$show_staff = false;
			$online = date($date_format, strtotime("-$offline_limit minutes"));
           	$staff_result = mysqli_query($link, "SELECT hacker.id FROM hacker INNER JOIN hacker_permgroup on hacker.id = hacker_permgroup.hacker_id WHERE (permgroup_id = 1 or permgroup_id = 2) AND invisible = 0 AND last_click >= '".$online."'");
			if(mysqli_num_rows($staff_result) > 0) $show_staff = true;
			
			$show_friends = false;
			$friends_result = mysqli_query($link, "SELECT hacker.id FROM multi_list INNER JOIN hacker ON multi_list.hacker_id2 = hacker.id WHERE invisible = 0 AND hacker_id1 = {$hackerdata['id']} AND relation = 'friend' AND invisible = 0 AND last_click >= '".$online."'");
			if($hackerdata['show_friends'] == 1 && mysqli_num_rows($friends_result) > 0) $show_friends = true;
			
			$show_foes = false;
			$foes_result =  mysqli_query($link, "SELECT hacker.id FROM multi_list INNER JOIN hacker ON multi_list.hacker_id2 = hacker.id WHERE invisible = 0 AND hacker_id1 = {$hackerdata['id']} AND relation = 'foe' AND invisible = 0 AND last_click >= '".$online."'");
			if($hackerdata['show_foes'] == 1 && mysqli_num_rows($foes_result) > 0) $show_foes = true;
		?>
		<?php if($show_ctf) { ?>
			<div class="slide-in-box" id="ctf-panel">
				<?php echo ShowFileHolder(); ?>
			</div>
		<?php } ?>
        
		<?php if($show_staff) { ?>
		<div class="slide-in-box" id="online-staff">
			<?php
				$alias = '';
				while($row = mysqli_fetch_assoc($staff_result))
				$alias .= ShowHackerAlias($row['id'], 0, 0, 0, true, 0, 0, 0).' & ';
				$alias = substr($alias, 0, -2);
				echo $alias;
			?>
        </div>
		<?php } ?>
		
		<?php if($show_friends) { ?>
		<div class="slide-in-box" id="online-friends">
			<?php
				$alias = '';
				while($row = mysqli_fetch_assoc($friends_result))
					$alias .= ShowHackerAlias($row['id'], 0, 0, 0, true, 0, 0, 0).',';
				$alias = substr($alias, 0, -1);
				echo $alias;
			?>
		</div>
		<?php } ?>
		
		<?php if($show_foes) { ?>
		<div class="slide-in-box" id="online-foes">
			<?php
				$alias = '';
				while($row = mysqli_fetch_assoc($foes_result))
					$alias .= ShowHackerAlias($row['id'], 0, 0, 0, true, 0, 0, 0).',';
				$alias = substr($alias, 0, -1);
				echo $alias;
			?>
		</div>
		<?php } ?>
<!-- Start of StatCounter Code for Default Guide -->
<script type="text/javascript">
var sc_project=4212184; 
var sc_invisible=1; 
var sc_security="0b7a268d"; 
var scJsHost = (("https:" == document.location.protocol) ?
"https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='" +
scJsHost+
"statcounter.com/counter/counter.js'></"+"script>");
</script>
<noscript><div class="statcounter"><a title="web stats"
href="http://statcounter.com/free-web-stats/"
target="_blank"><img class="statcounter"
src="http://c.statcounter.com/4212184/0/0b7a268d/1/"
alt="web stats"></a></div></noscript>
<!-- End of StatCounter Code for Default Guide -->
    </body>
	
</html>