var settings = {
    // Server date/time string
    serverTime : "<?php print date('F d, Y H:i:s', time())?>",
    
    // URLs of files called through AJAX (inbox count, chat mention)
    ajax : {
        inbox : "pages/ajax_inboxcount.php?id=<?php echo $hackerdata['id'].'&code='.sha1($hackerdata['started'].$hackerdata['last_login']); ?>",
        chat  : "pages/ajax_chatmention.php?id=<?php echo $hackerdata['id'].'&code='.sha1($hackerdata['started'].$hackerdata['last_login']); ?>",
    },
    
    // Sound notification settings
    sound : {
        play : <?php echo $hackerdata['sound_email']; ?>, // Sounds enabled? 1 = yes, 0 = no
        file : "sounds/email.mp3"
    },
    
    // Hack counters status
    counters : {
        scount : <?php if (isset($_SESSION['hacker_id'])) echo @SecondsDiff($now,$_SESSION['nextserverhack_date']); else echo "0"; ?>,
        pcount : <?php if (isset($_SESSION['hacker_id'])) echo @SecondsDiff($now,$_SESSION['nextpchack_date']); else echo "0"; ?>,
        jcount : <?php if (isset($_SESSION['hacker_id'])) echo @SecondsDiff($now,$_SESSION['nextjob_date']); else echo "0"; ?>,
        mcount : <?php if (isset($_SESSION['hacker_id'])) echo @SecondsDiff($now,$_SESSION['nextnpc_date']); else echo "0"; ?>,
        dcount : <?php if (isset($_SESSION['hacker_id'])) echo @SecondsDiff($now,$dcount); else echo "0"; ?>,
        acount : <?php if (isset($_SESSION['hacker_id'])) echo @SecondsDiff($now,$_SESSION['scan_till']); else echo "0"; ?>,
        countdown : <?php if (isset($_SESSION['countdown'])) echo $_SESSION['countdown']; else echo "0"; ?>
    },
  
    ep : <?php if(isset($_SESSION['ep'])) echo $_SESSION['ep']; else echo "0"; ?>,
};