<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php If(!InGroup($hackerdata['id'], 1) && !InGroup($hackerdata['id'], 2)) { AddLog($hackerdata['id'], "hacker", "abuse", "Tried to open a mod page: $page2load"); die(); } ?>
<?php
    $action = sql($_POST['action']);
    if ($action == "add") 
    {
        $message = sql($_POST['message']);
        if ($message == "") return ("Staff message can not be empty!");
        $result = mysqli_query ($link, "INSERT INTO ad (hacker_id, date, message) VALUES (0, '0', '$message')");
        PrintMessage ("info", "Staff message added.");
    }

    if ($action == "remove") 
    {
        $result = mysqli_query ($link, "DELETE FROM ad WHERE date = '0'");
        PrintMessage ("info", "Staff message removed.");
    }
?>