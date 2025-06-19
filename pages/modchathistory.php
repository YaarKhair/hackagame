<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
<?php
    // Not an admin or mod?
    $admin = InGroup($hackerdata['id'], 1);
       $mod = InGroup($hackerdata['id'], 2);
   	if(!$admin && !$mod) return 'Session error.';
 	
    // Get messages by date 
    echo "<form action='index.php' method='GET'>";
    echo "<input type='hidden' name='h' value='modgetchat'/>";
   	echo "<table>";
   	echo "<caption>Get Chat History By Date And Alias(optional)</caption>";
   	echo "<tr><th>Start Date</th><th>End Date</th><th>Alias</th><th>Limit</th></tr>";
   	echo "<tr><td><input type='text' name='start_date' value='".Number2Date($now, false)."'/></td>";
   	echo "<td><input type='text' name='end_date' value='".Number2Date($now, false)."'/></td>";
    echo "<td><input type='text' name='alias'/></td>";
    echo "<td><input type='text' name='limit' value='100'/></td></tr>";
    echo "<tr><td colspan='4'><input type='submit' value='Get Chat'/></td></tr>";
   	echo "</table><br><br>";
   	echo "</form>";

    include('pages/modgetchat.php');
?>