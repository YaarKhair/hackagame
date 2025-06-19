<?php
	// Initial values
	$game_result = '';
	$player_numcards = 0;
	$player_score = 0;
	$bank_numcards = 0;
	$bank_score = 0;
	$bet_placed = -1; // initial value
	$card = array();
	$image = array(
	"images/cards/h2.png" , "images/cards/h3.png" , "images/cards/h4.png" , "images/cards/h5.png" , "images/cards/h6.png" , "images/cards/h7.png" , "images/cards/h8.png" , "images/cards/h9.png" , "images/cards/h10.png" , "images/cards/h11.png" , "images/cards/h12.png" , "images/cards/h13.png" , "images/cards/h14.png" , 
	"images/cards/s2.png" , "images/cards/s3.png" , "images/cards/s4.png" , "images/cards/s5.png" , "images/cards/s6.png" , "images/cards/s7.png" , "images/cards/s8.png" , "images/cards/s9.png" , "images/cards/s10.png" , "images/cards/s11.png" , "images/cards/s12.png" , "images/cards/s13.png" , "images/cards/s14.png" , 
	"images/cards/d2.png" , "images/cards/d3.png" , "images/cards/d4.png" , "images/cards/d5.png" , "images/cards/d6.png" , "images/cards/d7.png" , "images/cards/d8.png" , "images/cards/d9.png" , "images/cards/d10.png" , "images/cards/d11.png" , "images/cards/d12.png" , "images/cards/d13.png" , "images/cards/d14.png" , 
	"images/cards/c2.png" , "images/cards/c3.png" , "images/cards/c4.png" , "images/cards/c5.png" , "images/cards/c6.png" , "images/cards/c7.png" , "images/cards/c8.png" , "images/cards/c9.png" , "images/cards/c10.png" , "images/cards/c11.png" , "images/cards/c12.png" , "images/cards/c13.png" , "images/cards/c14.png");
	$value = array(
	"2" , "3" , "4" , "5" , "6" , "7" , "8" , "9" , "10" , "10" , "10" , "10" , "11" , 
	"2" , "3" , "4" , "5" , "6" , "7" , "8" , "9" , "10" , "10" , "10" , "10" , "11" , 
	"2" , "3" , "4" , "5" , "6" , "7" , "8" , "9" , "10" , "10" , "10" , "10" , "11" , 
	"2" , "3" , "4" , "5" , "6" , "7" , "8" , "9" , "10" , "10" , "10" , "10" , "11");
	$aces = array(12,25,38,51);
	
	// welcome
	echo "<img src=\"images/blackjack.jpg\" align=\"right\">Welcome to the HackerForever Casino. Here you can play BlackJack.<br><br>The rules:<br>1. You can bet any amount you desire<br>2. No surrender<br>3. No split<br>4. Bank folds on any hand above player<br>5. No refunds<br><br>Good luck playing in the casino!<br><br>";

	// actions
	$action = "placebet";
	if (!empty($_REQUEST['action']))  $action = sql($_REQUEST['action']);
	
	Function ShuffleDeck() {
		Global $card;
		
		// shuffle a new deck
		$deck_number = 0;
		while ($deck_number < 52) {
			mt_srand ((double) microtime() * 9999999); // random seeder
			$card[$deck_number] = mt_rand (0,51); // create random card
			$unique = true; // lets presume it's unique
			
			if ($deck_number > 0) { // if there is more then 1 card in the deck, lets check if our new card is unique
				for ($j = 0; $j < $deck_number; $j++) {
					if ($card[$deck_number] == $card[$j]) $unique = false; // we found a match, so lets not add the new card to the deck
				}
			}	
			if ($unique) {
				//$_SESSION['s_card'.$deck_number] = $card[$deck_number]; // store in the session
				$deck_number++;
			}	
		}
	}
	
	Function ReadSessionData() {
		Global $player_numcards;
		Global $bank_numcards;
		Global $card;
		Global $game_result;
        Global $bet;
        Global $bet_placed;
		
		// read session
		if (isset($_SESSION['s_player_numcards'])) $player_numcards = $_SESSION['s_player_numcards'];
		if (isset($_SESSION['s_bank_numcards'])) $bank_numcards = $_SESSION['s_bank_numcards'];
    	if (isset($_SESSION['s_game_result'])) $game_result = $_SESSION['s_game_result'];
    	if (isset($_SESSION['s_bet'])) $bet = $_SESSION['s_bet'];
    	if (isset($_SESSION['s_bet_placed'])) $bet_placed = $_SESSION['s_bet_placed'];

		// read the shuffled deck back from the session
		for ($i = 0; $i < 52; $i++) {
			if (isset($_SESSION['s_card'.$i])) $card[$i] = $_SESSION['s_card'.$i];
		}	
	}
	
	Function WriteSessionData() {
		Global $player_numcards;
		Global $bank_numcards;
		Global $card;
		Global $game_result; // remember last result to prevent refreshing
		Global $bet;
        Global $bet_placed;
        
		$_SESSION['s_player_numcards'] = $player_numcards;
		$_SESSION['s_bank_numcards'] = $bank_numcards;
    	$_SESSION['s_game_result'] = $game_result;
    	$_SESSION['s_bet'] = $bet;
    	$_SESSION['s_bet_placed'] = $bet_placed;
    	
		// write the shuffled deck back to the session
		for ($i = 0; $i < 52; $i++) {
			if (isset($card[$i])) $_SESSION['s_card'.$i] = $card[$i];
		}	
    	
	}
	
	Function PlayerScore() {
		Global $player_score;
		Global $player_numcards;
		Global $value;
		Global $card;
		Global $aces;
		Global $game_result;
		
		// loop through deck from the left			
		$player_score = 0;
		$ace = 0; // no aces in the deck, that we know of
		for ($i = 0; $i < $player_numcards; $i++) {
			$player_score+=$value[$card[$i]];
			if (in_array($card[$i], $aces)) $ace = $ace + 1; // count the aces
		}
		// if we are above 21 but have aces in our hand, we will count them as 1 instead of 11
		while ($player_score > 21 AND $ace > 0){ // if you are above 21 and have aces, lets make them 1 instead of 11
			$ace = $ace - 1; // substract 1 ace
			$player_score = $player_score - 10; // substract 10 points
		}
		if ($player_score > 21) $game_result = "bust";
		
	}
	
	Function BankScore() {
		Global $bank_score;
		Global $bank_numcards;
		Global $value;
		Global $card;
		Global $aces;
		
		// loop through deck from the left			
		$bank_score = 0;
		$ace = 0; // no aces in the deck, that we know of
		for ($i = 51; $i > 51 - $bank_numcards; $i--) {
			$bank_score+=$value[$card[$i]];
			if (in_array($card[$i], $aces)) $ace = $ace + 1; // count the aces
		}
		// if we are above 21 but have aces in our hand, we will count them as 1 instead of 11
		while ($bank_score > 21 AND $ace > 0){ // if you are above 21 and have aces, lets make them 1 instead of 11
			$ace = $ace - 1; // substract 1 ace
			$bank_score = $bank_score - 10; // substract 10 points
		}
	}
	
	Function ShowPlayerCards() {
		Global $player_numcards;
		Global $player_score;
		Global $image;
		Global $card;
        Global $bet;
        Global $currency;
		
		echo '<div class="row light-bg"><div class="col w50">
					<h2>Your Hand (bet: '.$currency.$bet.')</h2>
					';
					
		// loop through deck from the left			
		for ($i = 0; $i < $player_numcards; $i++) {
			echo '<img src="'.$image[$card[$i]].'"><br><br>';
		}
		echo 'Total: '.$player_score;
		echo '</div>';
	}
	
	Function ShowBankCards() {
		Global $bank_numcards;
		Global $bank_score;
		Global $image;
		Global $card;
		
		echo '<div class="col w50">
				<h2>Bank Hand</h2>';
					
		// loop through deck from the left			
		for ($i = 51; $i > 51 - $bank_numcards; $i--) {
			echo '<img src="'.$image[$card[$i]].'"><br><br>';
		}
		if ($bank_numcards == 1) echo '<img src="images/cards/back.png"><br><br>';
		echo 'Total: '.$bank_score;
		echo '</div></div>';
	}
	
	Function PlayBankHand() {
		Global $bank_numcards;
		Global $bank_score;
        Global $player_score;
		
		BankScore(); // calculate current score
		
//		while ($bank_score < 17 || $bank_score < $player_score) {	// play until we reach at least 17
		while ($bank_score < $player_score) {	// play until we beat the players hand
			$bank_numcards ++;
			BankScore(); // calculate new score
		}	
	}
	
	Function CalculateWinner() {
		Global $game_result;
		Global $bank_score;
		Global $player_score;
		Global $player_numcards;
		Global $bank_numcards;
		
		if ($player_score == 21) {
			if ($player_numcards == 2) {
				if ($bank_score == 21 && $bank_numcards == 2) $game_result = "tie";
				else $game_result = "blackjack";
			}
			else {
				if ($bank_score == 21 && $bank_numcards == 2) $game_result = "lost";
				elseif ($bank_score == 21) $game_result = "tie";
				else $game_result = "win";
			}
		}
		elseif ($player_score > 21) $game_result = "bust";
		elseif ($bank_score > 21) $game_result = "win";
		else {
			// you have 20 or lower
			if ($player_score == $bank_score) $game_result = "tie";
			elseif ($player_score < $bank_score) $game_result = "lost";
			else $game_result = "win";
		}	
	}	
	
	Function PayOut() {
		Global $game_result;
		Global $bet;
		Global $hackerdata;
		
    	if ($game_result == "tie") {
			BankTransfer ($hackerdata['id'], "hacker", $bet, "Casino - BlackJack");
			//Gamble ($hackerdata['id'], $bet);
		}
		if ($game_result == "win") {
			BankTransfer ($hackerdata['id'], "hacker", $bet * 2, "Casino - BlackJack");
			Gamble ($hackerdata['id'], $bet);
		}
		if ($game_result == "bust" || $game_result == "lost") {
			//BankTransfer ($hackerdata['id'], "hacker", $bet*-1, "Casino - BlackJack");
			Gamble ($hackerdata['id'], $bet*-1);
		}
		if ($game_result == "blackjack") {
			BankTransfer ($hackerdata['id'], "hacker", $bet * 2.5, "Casino - BlackJack");
			Gamble ($hackerdata['id'], $bet*1.5);
		}
	}
	
	Function ShowOptions($bet) {
		Global $game_result;
        		
		if ($game_result != '') { // game is done, show Deal Again button
			PrintMessage ("Info", "Game Result: $game_result");
			echo '<input type="button" value="Deal Again" onclick="redirect(\'?h=doblackjack&action=placebet&bet='.$bet.'\');">';
		}	
		else {
			echo '<input type="button" value="Hit" onclick="redirect(\'?h=doblackjack&action=hit\');">   ';
			echo '<input type="button" value="Stand" onclick="redirect(\'?h=doblackjack&action=stand\');">';
		}
	}
	Function InitNewGame() {
		Global $player_numcards;
		Global $bank_numcards;
		Global $game_result;
		
   		$player_numcards = 2; // 2 initial cards for player
   		$bank_numcards = 1; // 1 open card for the bank
   		$game_result = '';
   		ShuffleDeck();
	}
	
	ReadSessionData(); // read the values
	
	// the player pressed BACK during a hand OR returned to the table after walking away from a hand before
	if ($action == "placebet" && $bet_placed == 1) {
    		PrintMessage ("Error", "Please finish the current game first");
    		$action = "hit";
	}		
	
	// player starts a new game
	if ($action == "placebet") {
            $bet = 0;
            if (!empty($_GET['bet'])) $bet = intval($_GET['bet']);
        echo '
            <form method="post" action="index.php">
                <input type="hidden" name="h" value="doblackjack">
                <input type="hidden" name="action" value="start_dealing">
                <input type="text" name="bet" value="'.$bet.'">
                <input type="submit" value="Place bet">
            </form>    
                ';
	}
    else {
    	
    	// initial hand
        if ($action == "start_dealing") {
        	if ($bet_placed == 1) return "You are already playing a hand. Finish that hand first"; // if the bet is already set, then a game is already running. better kill this one.
        	
        	// check the bet
        	$bet = 0;
            if (!empty($_POST['bet'])) $bet = abs(intval($_POST['bet']));
            if ($bet == 0) return "Bet not set. Aborting game.";
            if ($bet > $hackerdata['bankaccount']) return "You can not afford betting $bet";
            
            // pay the bet
            BankTransfer ($hackerdata['id'], "hacker", $bet*-1, "Casino bet");
        	$bet_placed = 1;
            
            // new hand
            InitNewGame();
    	}
    	if ($bet_placed != 1) return "Don't refresh this page.";
    	
    	// add a card.
    	if ($action == "hit") $player_numcards++;
    	
    	PlayerScore(); // before ShowCards();
    	ShowPlayerCards();
    	
    	if ($action == "stand" && $bet_placed == 1) {
    		if ($player_score <= 21) PlayBankHand(); // before ShowBankCards() !
    		BankScore();
    		CalculateWinner();
    	}	
    	else BankScore();
    	
    	ShowBankCards();
    	ShowOptions($bet);
    	
    	// game has ended, we have a result
    	if ($game_result != '') {
            ShuffleDeck(); // when people refresh the results page they get new cards  (IMPORTANT!)
    		PayOut();
            $bet_placed = 0; // round over, you can bet again.
            $action = "placebet";
            $result = '';
    	}
    }
   	WriteSessionData(); // write the values
?>