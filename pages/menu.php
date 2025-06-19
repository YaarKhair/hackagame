                <label for="mobile-nav" class="mobile-only"></label>
				<input type="checkbox" id="mobile-nav" class="mobile-only">
				<nav>
                    <ul>
                        <li>
                            <a href="#">Hacker</a>
                            <ul>
                                <li><a href="?h=profile">Profile</a></li>
                                <li><a href="?h=perks">Perks</a></li>
                                <li><a href="?h=dobankaccount&account=hacker">Bank</a></li>
                                <li><a href="?h=list">Friend, Foe, Ignore List</a></li>
                                <li><a href="?h=personalstats">Stats</a></li>
                                <li><a href="?h=achievements">Achievements</a></li>
                                <li><a href="?h=tickets&show=my">My Support Tickets</a></li>
								<?php if ($hackerdata['donator_till'] < $now || $hackerdata['network_id'] != 2) { ?><li><a href="?h=premiumhacker">GET PREMIUM!</a></li><?php } ?>
                            </ul>
                        </li>
                        <li>
                            <a href="#">Clan</a>
                            <ul>
								<?php if($hackerdata['clan_id'] > 0) { ?>
                                <li><a href="?h=claninfo">Info</a></li>
                                <li><a href="?h=forum&board_id=<?php echo mysqli_get_value("id", "board", "clan_id", $hackerdata['clan_id']); ?>">Forums</a></li>
                                <li><a href="?h=members">Members</a></li>
								<?php if($hackerdata['clan_council'] == 1) { ?><li><a href="?h=writeclanim">Message Members</a></li> <?php } ?>
                                <li><a href="?h=clanwar">Clan War</a></li>
                                <li><a href="?h=dobankaccount&account=clan">Bank</a></li>
								<li><a href="?h=claninvite">Invites</a></li>
                                <li><a href="?h=leaveclan&code=<?php echo sha1($hackerdata['started'].$hackerdata['last_login']); ?>" onclick="return confirm('Are you sure you want to leave your clan?');">Leave Clan</a></li><?php } else { ?>
								<li><a href="?h=startclan">Start A New Clan</a></li> <?php } ?>
                            </ul>
                        </li>
                        <li>
                            <a href="#">Commerce</a>
                            <ul>
                                <li><a href="?h=shop&shop=isp">SkyNet</a></li>
                                <li><a href="?h=shop&shop=hardware">1338 Hardware Shop</a></li>
                                <li><a href="?h=shop&shop=software">1338 Software Shop</a></li>
                                <li><a href="?h=shop&shop=perk">1338 Perk Shop</a></li>
                                <li><a href="?h=convention">Visit ConDef</a></li>
                                <li><a href="?h=ad">Buy an Ad</a></li>
								<li><a href="?h=lottery">CheapCash Lottery</a></li>
                                <li><a href="?h=doblackjack">BlackJack</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">World</a>
                            <ul>
                                <li><a href="?h=online">Who's Active?</a></li>
                                <li><a href="?h=bountyboard">Bounty Board</a></li>
                                <li><a href="?h=economy">Economy</a></li>
                                <li><a href="?h=clans">Active Clans</a></li>
                                <li><a href="?h=clanwars">Active Wars</a></li>
                                <li><a href="?h=warhistory">War History</a></li>
                                <li><a href="?h=stats">Stats</a></li>
                             	<li><a href="?h=worldrankep">World Rank List (EP)</a></li>
                             	<li><a href="?h=worldrankhp">World Rank List (HP)</a></li>
                                <li><a href="?h=ctf">Capture The File</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">Legal</a>
                            <ul>
                                <li><a href="?h=jail">County Jail</a></li>
                                <li><a href="?h=prison">Prison</a></li>
                                <li><a href="?h=fbimostwanted">FBI Most Wanted List</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">Support</a>
                            <ul>
							<?php
							$changelognew = '';
							$changelog = mysqli_get_value_from_query("SELECT lastchangelogentry_date FROM misc", "lastchangelogentry_date");
							if($changelog >= $hackerdata['lastchangelogvisit_date']) $changelognew = '<span class="note"><strong>NEW</strong></span>';
							?>
                                <li><a href="?h=wiki">Wiki</a></li>
                                <li><a href="?h=wiki&title=N00b+Guide">n00b Guide</a></li>
                                <li><a href="?h=wiki&title=Game+Manual">Game Manual</a></li>
                                <li><a href="?h=wiki&title=Rules+and+Regulations">Rules and regulations</a></li>
                                <li><a href="?h=tickets">All Support Tickets</a></li>
								<!--<li><a href="?h=changelog">Changelog <?php echo $changelognew; ?></a></li>//-->
                            </ul>
                        </li>
                        <li>
                            <a href="#">Community</a>
                            <ul>
                                <li><a href="?h=forum&clan_id=0&board_id=370">Public Forums</a></li>
                                <li><a href="?h=referral" class="emphasis">Invite friends, get cash</a></li>
                                <li><a href="?h=banlist">Hall Of Shame</a></li>
                                <li><a href="?h=countries">Country List</a></li>
                                <li><a href="?h=games">Mini Games</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
