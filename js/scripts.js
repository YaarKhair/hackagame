<script language="JavaScript" type="text/javascript">
<!--
function redirect(link) {
	document.location = link;
}
function newCookie(name,value,days) {
	var expires = '';
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		expires = "; expires="+date.toGMTString(); 
	}
	else {expires = "";}
	document.cookie = name+"="+value+expires+"; path=/"; 
}
function readCookie(name) {
	var nameSG = name+"=";
/*	if (document.cookie.indexOf(nameSG) == -) {return '';}*/
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameSG) == 0) return c.substring(nameSG.length,c.length); 
	}
    return null; 
}
function eraseCookie(name) {
	newCookie(name,"",-1); 
}
/* check or uncheck all checkboxes in a form */
function checkAll(field,value) {
	// if the array has just one element the for loop won't work as the array will be undefined. if so, lets treat it like there is only one message, and just toggle that one.
	if (typeof(field.length) == "undefined") field.checked = value;
	for (i = 0; i < field.length; i++)
		field[i].checked = value;
} 
/*********************
 * update inbox count and play sound
 *********************/
 function InboxCountResponse() {    
	if (httpinbox.readyState == 4) { 
		if(httpinbox.status==200) { 
			var results=httpinbox.responseText; 
			if (results > 0) {
				document.title = results + " New Message(s)";
				document.getElementById('inboxcount').innerHTML = '<span style="color:red; font-weight:bold;">' + results + '</span>'; 
				var inboxcount = readCookie('inbox');
				if (inboxcount === undefined) {inboxcount = 0;}
				var sound_email = <?php echo $hackerdata['sound_email']; ?>;
				if (results > inboxcount && sound_email == 1) {
					document.getElementById("sound_element").innerHTML= "<audio autoplay='true' src='/sounds/email.mp3'>";
					eraseCookie('inbox');
					newCookie('inbox', results, 7); // store the number of messages in your inbox
				}
				else  {document.getElementById("sound_element").innerHTML= '';}
			}
			else {
				document.title = "<?php echo $page_title; ?>";
				document.getElementById('inboxcount').innerHTML = results;
				eraseCookie('inbox');
				newCookie('inbox', 0, 7);
				document.getElementById("sound_element").innerHTML= '';
			}	 
		} 
	} 
} 
/*********************
 * chat mentioned? play sound
 *********************/
 function ChatMentionResponse() {
 	var mention = <?php echo $hackerdata['chat_alert']; ?>;
	if(mention == 0) return false;
	if (httpchatmention.readyState == 4) { 
		if(httpchatmention.status==200) { 
			var results=httpchatmention.responseText; 
			if (results > 0) {
				document.title = "You got mentioned in chat!";
				var sound_email = <?php echo $hackerdata['sound_email']; ?>;
				document.getElementById("sound_element").innerHTML= "<audio autoplay='true' src='/sounds/email.mp3'>";
			}
			else {
				document.title = "<?php echo $page_title; ?>";
				document.getElementById("sound_element").innerHTML= '';
			}	 
		} 
	} 
} 
// AJAX THE INBOX COUNTER		
function requestInboxCount() {      
	<?php if ($hackerdata['id'] == 0) echo "return false;"; ?>
	httpinbox = getHTTPObject(); 
	httpinbox.open("GET", "pages/ajax_inboxcount.php?id=<?php echo $hackerdata['id'].'&code='.sha1($hackerdata['started'].$hackerdata['last_login']); ?>", true); 
	httpinbox.onreadystatechange = InboxCountResponse; 
	httpinbox.send(null); 
	setTimeout('requestInboxCount()',60000);
} 
// AJAX THE CHAT MENTION
function requestChatMention() {      
	<?php if ($hackerdata['id'] == 0) echo "return false;"; ?>
	httpchatmention = getHTTPObject(); 
	httpchatmention.open("GET", "pages/ajax_chatmention.php?id=<?php echo $hackerdata['id'].'&code='.sha1($hackerdata['started'].$hackerdata['last_login']); ?>", true); 
	httpchatmention.onreadystatechange = ChatMentionResponse; 
	httpchatmention.send(null); 
	setTimeout('requestChatMention()',30000);
} 
/*********************
 * end of update inbox count
 *********************/
// Get the HTTP Object
function getHTTPObject(){
 if (window.ActiveXObject) return new ActiveXObject("Microsoft.XMLHTTP");
 else if (window.XMLHttpRequest) return new XMLHttpRequest();
 else {
    alert("Your browser does not support AJAX.");
    return null;
 }
}   
      
function confirmSubmit(message)
{
var agree=confirm(message);
if (agree){return true ;}
else {return false ;}
}
function quote(message) {
	document.hf_form.message.value += message;
}

function saveAccordionStatus(id) {
	var log;
    // Current status of accordion box
    var status = document.getElementById(id).checked;
	if(status == true) log = false;
	if(status == false) log = true;
	//console.log(log);
    // Save
    newCookie(id, log, 365);
}

function retrieveAccordionStatus(id) {
    // Saved status of accordion box
    var status = readCookie(id);
    // Set to saved status
    document.getElementById(id).checked = status;
}

function memStatus() {
    // Array of accordion boxes to check
    var menuAccordions = new Array("ac-admin");
    
    for (var i=0; i < menuAccordions.length; i++) {
        retrieveAccordionStatus(menuAccordions[i]);
    }
}

// break out of frames including iframes
if (top.location != self.location) {
top.location = self.location.href
}

var currenttime = '<?php print date("F d, Y H:i:s", time())?>' //PHP method of getting server date
var montharray=new Array("January","February","March","April","May","June","July","August","September","October","November","December")
var serverdate=new Date(currenttime)
//console.log(serverdate);

function Seconds2Time(seconds, showdays) {
	var numdays = Math.floor(seconds / 86400);
	var numhours = Math.floor((seconds % 86400) / 3600);
	var numminutes = Math.floor(((seconds % 86400) % 3600) / 60);
	var numseconds = ((seconds % 86400) % 3600) % 60;
	var returntime = '';
	
	// only show if > 0
	if (numhours > 0) returntime = returntime + numhours + "h:";
	if (numminutes > 0) returntime = returntime + numminutes + "m:";
	/*if (numseconds > 0)*/ returntime = returntime + numseconds + "s";
	
	// via parameter
	if (showdays == 1) returntime = numdays + "d:" + returntime;
	return returntime;
}
function padlength(what){
	var output=(what.toString().length==1)? "0"+what : what;
	return output;
}

function displaytime(){
	<?php if (!isset($_SESSION['hacker_id'])) echo 'return false;'.PHP_EOL; ?>
	serverdate.setSeconds(serverdate.getSeconds()+1)
	//var datestring=montharray[serverdate.getMonth()]+" "+padlength(serverdate.getDate())+", "+serverdate.getFullYear()
	var timestring=padlength(serverdate.getHours())+":"+padlength(serverdate.getMinutes())+":"+padlength(serverdate.getSeconds())
	document.getElementById("servertime").innerHTML=timestring;
}
/* global vars */
var scount=0; //serverhack
var pcount=0; //pchack
var jcount=0; //smallhack
var mcount=0; //contract
var dcount=0; //downloads
var acount=0; //antivirus

function HackCounters()
{
	<?php if (!isset($_SESSION['hacker_id'])) echo 'return false;'.PHP_EOL; ?>
	scount=scount-1;
	pcount=pcount-1;
	jcount=jcount-1;
	mcount=mcount-1;
	dcount=dcount-1;
	acount=acount-1;
	var countdown = document.getElementById("scount");
	if(scount > -1) countdown.innerHTML="<span style=\"color:red\">"+Seconds2Time(scount)+"</span>";
	else countdown.innerHTML="<span>READY!</span>";
	var countdown = document.getElementById("pcount");
	if(pcount > -1) countdown.innerHTML="<span style=\"color:red\">"+Seconds2Time(pcount)+"</span>";
	else countdown.innerHTML="<span>READY!</span>";
	var countdown = document.getElementById("jcount");
	if(jcount > -1) countdown.innerHTML="<span style=\"color:red\">"+Seconds2Time(jcount)+"</span>";
	else countdown.innerHTML="<span>READY!</span>";
	var countdown = document.getElementById("mcount");
	if(mcount > -1) countdown.innerHTML="<span style=\"color:red\">"+Seconds2Time(mcount)+"</span>";
	else countdown.innerHTML="<span>READY!</span>";
	var countdown = document.getElementById("dcount");
	if(dcount > -1) countdown.innerHTML="<span style=\"color:red\">"+Seconds2Time(dcount)+"</span>";
	else countdown.innerHTML="<span>READY!</span>";
	var countdown = document.getElementById("acount");
	if(acount > -1) countdown.innerHTML="<span style=\"color:red\">"+Seconds2Time(acount)+"</span>";
	else countdown.innerHTML="<span>READY!</span>";
}
var httpinbox;
var dummy1;

window.onload=function(){
	requestInboxCount();
	requestChatMention();
	setInterval("displaytime()", 1000);
	memStatus();
}
//-->
</script>