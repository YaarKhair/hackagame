var Chat = {
    key: "<?php echo sha1($hackerdata['last_login'].$hackerdata['started']); ?>",
    textField: document.getElementById("msg"),
    prevMessage: "",
    init: function() {
        Chat.actionsMonitor();
        Chat.updateChat();
        Chat.updateUsersList()
    },
    actionsMonitor: function() {
        Chat.textField.addEventListener("keyup", function(a) {
            if (a.keyCode == 13) {
                Chat.sendMessage();
                Chat.textField.value = ""
            }
            if (a.keyCode == 38) {
                Chat.getPrevMessage()
            }
        });
        document.getElementById("chat-button").addEventListener("click", function(a) {
            Chat.sendMessage();
            Chat.textField.value = "";
            a.preventDefault()
        })
    },
    updateOutput: function(a) {
        if (a === "kicked" || a === "offline" || a === "banned" || a === "hybernated" || a === "prison") {
            window.location.reload()
        } else {
            var b = document.getElementById("chat-window");
            b.innerHTML = a;
            b.scrollTop = b.scrollHeight
        }
    },
    sendMessage: function() {
        var a = "chat.php?msg=" + encodeURIComponent(Chat.textField.value) + "&code=" + Chat.key;
        AJAX.load(a, function(b) {
            Chat.updateOutput(b)
        })
		Chat.prevMessage = Chat.textField.value;
    },
    updateChat: function() {
        var a = "chat.php?all=1&code=" + Chat.key;
        AJAX.load(a, function(b) {
            Chat.updateOutput(b)
        });
        setTimeout(Chat.updateChat, 5000)
    },
    updateUsersList: function() {
        var a = "pages/ajax_chatuserlist.php?code=" + Chat.key;
        AJAX.load(a, function(b) {
            if (b === "timeout" || b === "kicked" || b === "offline" || b === "banned" || b === "hybernated" || b === "prison") {
                window.location.reload()
            } else {
                var c = document.getElementById("users");
                c.innerHTML = b
            }
        });
        setTimeout(Chat.updateUsersList, 7000)
    },
    setPrevMessage: function() {
        if (Chat.textField.value !== "") {
            Chat.prevMessage = Chat.textField.value
        }
    },
    getPrevMessage: function() {
        Chat.textField.value = Chat.prevMessage
    },
    whisper: function(a) {
        Chat.textField.value = "/w|" + a + "|";
        Chat.textField.focus()
    }
};
Chat.init();