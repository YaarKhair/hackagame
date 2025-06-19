/*
 * Object containing all game functionality code.
 */
var HF = {
    
    /*
     * Variables
     */
    title : document.title,
    
    date  : new Date(settings.serverTime), // Convert server date string to JS usable format
    
    /*
     * Initiates these object methods upon page load.
     */
    init : function() {
        HF.breakOutOfFrames();
        HF.addConfirmations();
        HF.accordionRetrieveStatus();
        HF.accordionToggleView();
        HF.updateClock();
        HF.updateHackCounters();
        HF.requestInboxCount();
        HF.requestChatMention();
    },
    
    /*
     * Changes the page title.
     * If the argument is empty it reverts to the original title.
     */
    changeTitle : function (text) {
        if (text === null || text.length === 0) {
            text = HF.title;
        }
        document.title = text;
    },
    
    /*
     * Redirects to the specified url
     */
    redirect : function (url) {
        document.location = url;
    },
    
    /*
     * Function that adds a leading zero (0) in front of single-digit numbers
     */
    padlength : function (input) {
        return (input.toString().length === 1) ? "0" + input : input;
    },
    
    /*
     * Transforms a number of seconds to a readable time string.
     */
    seconds2time : function (seconds, showdays) {
        var numdays = Math.floor(seconds / 86400);
        var numhours = Math.floor((seconds % 86400) / 3600);
        var numminutes = Math.floor(((seconds % 86400) % 3600) / 60);
        var numseconds = ((seconds % 86400) % 3600) % 60;
        var returntime = "";

        // only show if > 0
        if (numhours > 0) returntime = returntime + numhours + "h:";
        if (numminutes > 0) returntime = returntime + numminutes + "m:";
        /*if (numseconds > 0)*/ returntime = returntime + numseconds + "s";

        // via parameter
        if (showdays === 1) returntime = numdays + "d:" + returntime;
        return returntime;
    },
    
    /*
     * Open a popup confirmation.
     * It will block any pending actions until answered "OK" or "Cancel".
     */
    confirmation : function (message) {
        if (message === null || message.toString().length === 0) {
            message = "Are you sure you want to proceed?";
        }
        return window.confirm(message);
    },
    
    /*
     * Check/Uncheck a set of checkboxes
     */
    checkAll : function (field, value) {
        // If there's only one checkbox the array will be undefined.
        // In this case we'll treat it as a single variable.
        if (typeof(field.length) === "undefined") {
            field.checked = value;
            return;
        }
        for (var i = 0; i < field.length; i++) {
            field[i].checked = value;
        }
    },
    
    /*
     * Prevents the page from loading inside a frame.
     * Will redirect to the full site version.
     * Improved script version that doesn't kill the "back" button.
     */
    breakOutOfFrames : function () {
        if (window.top.location !== window.location) {
            window.top.location.replace(window.location);
        }
    },
    
    /*
     * Play notification sound.
     */
    playSound : function () {
        if (settings.sound.play !== 1) {
            return false;
        }
        
        // Create sound object
        var sound = new Audio(settings.sound.file);
        
        // When finished playing, free memory
        sound.onended = function() {
            delete sound;
        };
        
        // Actually play the sound
        sound.play();
    },
    
    /*
     * 
     */
    addConfirmations : function () {
        var redButtons = document.querySelectorAll(".bg-red");
        
        for (var i=0; i < redButtons.length; i++) {
            redButtons[i].disabled = false;
            redButtons[i].addEventListener("click", function(e) {
                var message = this.getAttribute("title");
                if (HF.confirmation(message) === false) {
                    e.preventDefault();
                }
            });
        }
    },
    
    /*
     * Initiates the accordions based on their saved status and then monitors 
     * each accordion and saves its status in a cookie everytime it changes.
     */
    accordionRetrieveStatus : function () {
        // NodeList containing all accordions' hidden checkboxes.
        var checkboxList = document.querySelectorAll("input[type=checkbox].accordion-toggle");
        
        for (var i = 0; i < checkboxList.length; i++) {
            // Retrieve status from cookie
            var status = Cookie.read(checkboxList[i].id);
            
            if (status == "true" || (status === null && checkboxList[i].checked === true)) {
                addClass(checkboxList[i].parentNode, "checked");
            } else if (status !== null) {
                removeClass(checkboxList[i].parentNode, "checked");
            }
            //checkboxList[i].checked = (status.toLowerCase() === "true");
        }
    },
    
    /*
     * Accordion mechanism re-created with JavaScript aiming to fix
     * functionality on mobiles and especially Windows Phone.
     */
    accordionToggleView : function () {
        var labelList = document.querySelectorAll("input[type=checkbox].accordion-toggle + label");
        
        for (var i = 0; i < labelList.length; i++) {
            
            labelList[i].addEventListener("click", function(e) {
                // Cancel label's default behavior
                e.preventDefault();
                
                // Toggle view
                var target = this.getAttribute("for");
                var checkbox = document.getElementById(target);
                
                if (Cookie.read(checkbox.id) == "true") {
                    removeClass(this.parentNode, "checked");
                    Cookie.write(checkbox.id, "false", 365);
                    //checkbox.checked = false;
                } else {
                    addClass(this.parentNode, "checked");
                    Cookie.write(checkbox.id, "true", 365);
                    //checkbox.checked = true;
                }
                
                // Save status
                //Cookie.write(checkbox.id, checkbox.checked, 365);
            });
        }
    },
    
    /*
     * Adds real time functionality to the game clock.
     */
    updateClock : function () {
        
        var date = HF.date;
        
        //var months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
        //var date = months[date.getMonth()]+" "+padlength(date.getDate())+", "+date.getFullYear();
        
	date.setSeconds(date.getSeconds() + 1);
        
        var h = HF.padlength(date.getHours()),
            m = HF.padlength(date.getMinutes()),
            s = HF.padlength(date.getSeconds());
        
        // Update interface clock
	document.getElementById("servertime").innerHTML = h + ":" + m + ":" + s;
        
        // Update the clock every 1 second
        setTimeout(HF.updateClock, 1000);
    },
    
    /*
     * Adds real time functionality to the hack counters.
     */
    updateHackCounters : function() {
        for (var c in settings.counters) {
            settings.counters[c] -= 1;

            var countdown = document.getElementById(c.toString());
            if (countdown === null) continue;

            if (settings.counters[c] > -1) {
                countdown.innerHTML = '<span class="red">' + HF.seconds2time(settings.counters[c]) + '</span>';
            } else {
                countdown.innerHTML = '<span>READY!</span>';
            }
        }
        
        // Update the counters every 1 second
        setTimeout(HF.updateHackCounters, 1000);
    },
    
    /*
     * Updates the inbox counter recursively.
     * The check will run every 60 seconds.
     */
    requestInboxCount : function() {
        AJAX.load(settings.ajax.inbox, function(response) {
            var messages = parseInt(response); // Number of unread messages, sent by AJAX
            
            var newTitle = "";
            
            var inboxCount = parseInt(Cookie.read("inbox"));
            if (inboxCount === undefined) inboxCount = 0;
            
            if (messages > 0) {
				if (inboxCount != messages) { // If there are new messages
                	newTitle  = messages + " New Message";
                	newTitle += (messages !== 1) ? "s" : "";
                
                	HF.playSound();
				}
                // Adding HTML in here as we no longer need it as an integer
                inboxCount = '<span class="bold red">' + messages + '</span>';
            }
            
            // Update cookie, page title and interface counter
            Cookie.write("inbox", messages, 7);
            HF.changeTitle(newTitle);
            document.getElementById("inboxcount").innerHTML = inboxCount;
        });
        
        // Repeat check every 60 seconds
        setTimeout(HF.requestInboxCount, 60000);
    },
    
    /*
     * Checks if the player was mentioned in the chat.
     * The check will run every 30 seconds.
     */
    requestChatMention : function() {
        AJAX.load(settings.ajax.chat, function(response) {
            var newTitle = "";
            
            if (response > 0) { // If mentioned
                newTitle = "You got mentioned in chat!";
                HF.playSound();
            }
            
            // Update page title
            HF.changeTitle(newTitle);
        });
        
        // Repeat check every 30 seconds
        setTimeout(HF.requestChatMention, 30000);
    }
};



/*
 * Object containing the code for AJAX requests.
 */
var AJAX = {
    
    /*
     * Picks the appropriate XMLHTTP object and returns a new instance of that.
     */
    getHTTPObject : function() {
        var xhr = null;
        
        if (typeof XMLHttpRequest !== "undefined") {
            xhr = new XMLHttpRequest();
        } else { // Support for IE6
            var versions = ["MSXML2.XmlHttp.5.0",
                            "MSXML2.XmlHttp.4.0",
                            "MSXML2.XmlHttp.3.0",
                            "MSXML2.XmlHttp.2.0",
                            "Microsoft.XmlHttp"];  
            for(var i = 0; i < versions.length; i++) {
                try {
                    xhr = new ActiveXObject(versions[i]);
                    break;
                } catch (e) {}
            }
        }
        
        return xhr;
    },
    
    /*
     * Sends an AJAX request to the given url.
     * When done the specified callback function runs.
     * Very simple method (same as was before), no support of sending POST or data.
     * Example use:
     * AJAX.load("path/to/file.php", function() {
     *     //code
     * });
     */
    load : function(url, callback) {
        var xhr = AJAX.getHTTPObject();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = xhr.responseText;
                callback(response);
            }
        };
        
        xhr.open("GET", url, true);
        xhr.send(null);
    }
};



/*
 * Object containing the code for writing, reading and erasing cookies.
 */
var Cookie = {
    
    /*
     * Function for writing cookies.
     */
    write : function(name, value, days) {
        var content = name + "=" + escape(value) + ";"; // Cookie contents
        // Set expiration date
        if (days) {
            var expires = new Date(new Date().getTime() + parseInt(days) * 1000 * 60 * 60 * 24);
            content += "expires=" + expires.toGMTString() + ";";
        }
        // Write cookie
        document.cookie = content;
    },
    
    /*
     * Function for reading cookies.
     */
    read : function(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
	}
	return null;
    },
    
    /*
     * Function for erasing cookies. Uses the "cookieWrite" function.
     */
    erase : function(name) {
        if (Cookie.read(name)) Cookie.write(name, "", -1);
    }
};





function hasClass(elem, cls) {
  return !!elem.className.match(new RegExp('(\\s|^)' + cls + '(\\s|$)'));
}

function addClass(elem, cls) {
  if (!hasClass(elem, cls)) elem.className += " " + cls;
}

function removeClass(elem, cls) {
  if (hasClass(elem, cls)) {
    var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
    elem.className = elem.className.replace(reg,' ');
  }
}





// Alias of: HF.checkAll()
function checkAll(field, value) {
    HF.checkAll(field, value);
}

// Alias of: HF.confirmation()
function confirmSubmit(message) {
    HF.confirmation(message);
}

// Alias of: HF.redirect()
function redirect(link) {
    HF.redirect(link);
}

// Possibly not used anywhere.
function quote(message) {
    document.hf_form.message.value += message;
}





// Set things in motion!
window.onload = HF.init;
