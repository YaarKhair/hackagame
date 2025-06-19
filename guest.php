<?php
	session_start();
	include_once("modules/connectdb.php");
	include_once("modules/settings.php");
	include_once("modules/functions.php");
	
	// fill a hackerdata recordset for our guest.
	$hackerdata = Array();
	$hackerdata['id'] = 0;
	$hackerdata['clan_id'] = 0;
	$hackerdata['permissiongroup_id'] = 0;
	$hackerdata['clan_council'] = 0;
	$hackerdata['started'] = 0;
	$hackerdata['custom_logo'] = ''; // used in javascript, so errors if not set.
	$hackerdata['donator_till'] = 0; // used in javascript, so errors if not set.
	$hackerdata['last_login'] = 0; // used in javascript, so errors if not set.
	$hackerdata['custom_css'] = ''; // used in javascript, so errors if not set.
	$hackerdata['sound_email'] = 0;
	$hackerdata['show_ads'] = 1;
	$hackerdata['snow'] = 0; // no snow for guests
	$hackerdata['show_tooltips'] = 0;	// set it to 0 for guests
	$hackerdata['real_ip'] = GetUserIP();

	// page to load
	$page2load = "about"; // default page
	if (!empty($_REQUEST['h'])) $page2load = $_REQUEST['h'];
	$page2load = sql($page2load); // sanitize the input

	// Check if there's an referral
	$referral_code = '';
	if(!empty($_GET['referral_code'])) $_SESSION['referral_code'] = sql($_GET['referral_code']);
		
	// logged in? then you shouldn't be here.
	if($hackerdata['id'] > 0) header('location: index.php');
?>
<html>
    
    <head>
        <title>HackerForever | Welcome. Please sign up or sign in</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="theme/images/favicon.png">
        <link rel="stylesheet" href="theme/welcome.css" type="text/css">
        
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
    </head>
    
    <body>
        
        <header>
            <div class="bg-mask">
                
                <nav class="container">
                    <ul>
                        <li><a href="guest.php#join">Join</a></li>
                        <li><a href="guest.php#features">Features</a></li>
                        <li id="logo"></li>
                        <li><a href="guest.php#screenshots">Screenshots</a></li>
                        <li><a href="guest.php#login">Login</a></li>
                    </ul>
                </nav>
<?php if ($page2load != "hackthegame") { ?>
                <section id="about">
                    <div class="container center">
                        <h1>HackerForever is a text-based browser game based on the dark world of hackers.</h1>
                        <p>Change between play styles of white hat, grey hat or black hat. Become respected, feared or hated.</p>
                        <p>The game has a clan system, virtual hard- and software, a fictional network, several shops and online services, loads of tools, an active community, a chatbox and much much more.</p>
                        <br>
                        <h2>There are <span><?php echo number_format(mysqli_get_value_from_query("SELECT count(id) as num FROM hacker", "num")); ?></span> registered players currently. Wanna be one too?</h2>
                        <br><br>
                        <p><a href="guest.php#join" class="ghost-button">Join Now!</a></p>
                    </div>
                </section>
<?php } ?>				
            </div>
        </header>
		
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
			<?php        
				$fullpage2load = "./guestpages/".$page2load.".php";
				$return_code = 1;
				if (file_exists($fullpage2load)) {
					$return_code = include($fullpage2load);
					if ($return_code != 1) {
						PrintMessage ("Error", $return_code, "40%");
						include ("./guestpages/about.php"); // show forms after the error message
					}
				}
				else 
					include("./pages/404.php");
        	?>
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
		
        <footer>
            <div class="container row">
				<?php include ("pages/footer.php"); ?>
            </div>
        </footer>
        
    </body>
		<script src="js/formCheck.js?b=123456"></script> 
</html>