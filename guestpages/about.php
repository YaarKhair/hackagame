<?php if ($index_refer != "!@#$%^&*()_+") { die(); } ?>
		<section id="features">

           <div class="container row">
                <div class="col w20"><img src="theme/images/guest.png" alt="Feature"></div>
                <div class="col w80">
                    <p>
					The game revolves around a progression system in which you as the hacker can level up and become more powerful.<br>
					Will you make friends or enemies on your way to the top?<br>
                    </p>
                    <p>
					One of the biggest parts of the game is our community. They are there to help you when you get stuck.<br>
					You can create your own clan and invite others to join you in your battle to become the biggest and most powerful clan in game!<br>
                    </p>
                    <p>
					HackerForever works on desktops as well as mobile devices, you play the game from any location.<br>
					So you can always check how your system is doing, read through your system logs or attack a system.<br>
                    </p>
                </div>
        </section>

        <section id="join">
            <div class="container row">
                <h1>Join now for free!</h1>
                <div class="col w50">
                    
                    <form action="guest.php" method="post" id="registration-form">
						<input type="hidden" name="h" value="register">
                        <p>All form fields are required.<br>Be sure to read the rules before proceeding.</p>
                        <input type="text" id="username" placeholder="Username" class="icon user" name="alias">
                        <span>Alphanumeric and underscores. You can change this name later. </span>
                        <input type="email" id="email" placeholder="Email address" class="icon email" name="email">
                        <span>Private. Used for activation and password reset.</span>
                        <input type="password" id="password" placeholder="Password" class="icon pass" name="pass1">
                        <span>Must contain at least 1 lowercase letter, 1 uppercase and 1 symbol.</span>
                        <input type="password" placeholder="Repeat password" class="icon pass" name="pass2" id="password2">
                        <span>Your password again, please.</span>
                        <select name="country" class="icon country" name="country">
						<?php
							$result = mysqli_query($link, "SELECT * FROM country ORDER BY name ASC");
							if (mysqli_num_rows($result) != 0) {
								while ($row = mysqli_fetch_assoc($result)) {
									if (file_exists('images/flags/'.strtolower($row['code']).'.png')) echo '<option value="'.strtolower($row['code']).'">'.$row['name'];
								}
							}
						?>
                        </select>
						<p class="pv10"></p> 
						<p class="pv10"><input type="checkbox" id="agree" checked disabled><label for="agree">I have read and agree to the rules.</label></p>
                        <input type="submit" value="Join!" id="joinButton" disabled="true">&nbsp;&nbsp;&nbsp;
                        <input type="reset" value="Clear form">
                    </form>
					<br>
					<div class="message info">The game works on all the latest versions of major web browsers. The minimum recommended resolution for a great experience is 1366x768, but there are also tablet and mobile versions!</div>
                </div>
                <div class="col w50">
                    
                    <div class="accordion">
                        <input id="ac-rules" type="checkbox" class="accordion-toggle" checked="true">
                        <label for="ac-rules">Rules</label>
                        <div class="accordion-box">
							<h2>1. Multiple Accounts</h2>
							<p>
							You can only play with one (1) account per person. We do allow two (2) players per household for other members of your household.
							We do however monitor for cheating (sending money to eachother, being online simultaniously, etc) between the accounts.
							Multiple accounts are instant ban of the offending IP and the corresponding accounts.
							</p>

							<h2>2. Impersonation</h2>
							<p>
							Do not impersonate other hackers or the Game Administration. If you are caught impersonating it will first result in jailtime. A second violation of this rule will get you banned.
							</p>

							<h2>3. Bug Abuse</h2>
							<p>
							If you find a bug or glitch in the game, you must report it instantly. To report a bug, please message one of the staff members of open a Mods-Only support ticket.
							Depending on severity, bug abuse will result in anywhere from a jail sentence to a permanent ban.
							</p>

							<h2>4. English only</h2>
							<p>
							This game is english only. The only allowed language in the chat, on your hacker profile, on the clan profile and on the forums is english. 
							For IM's you are free to use any language you fancy.
							</p>

							<h2>5. Player Harassment</h2>
							<p>
							Do not harass other players. Game staff determines when a communication crosses the line into abusive. This does not mean everyone must play nice, because this is a competitive game after all. The harassment law only applies to continued and targeted harassment of a particular person, not the sporadic insult. 
							In general, private communications (IM) are just that, private, and not subject to decency or harassment laws. Players are encouraged to use the ignore list to stop harassment via IM. 
							</p>

							<h2>6. External tools</h2>
							<p>
							Do not use external tools of any kind (refreshers, rankers, real hacking tools, bots, macros etc). They give you an unfair advantage over other players. If we catch you were using one you face an instant ban.
							</p>

							<h2>7. External Links</h2>
							<p>
							You are not allowed to post external links in Public Forums and in the Game & Support Chat. 
							External links such as youtube, facebook, myspace, twitter, 9gag, ect. are allowed as long as they are appropriate.
							</p>

							<h2>8. Real Hacking Discussions</h2>
							<p>
							You are not allowed to discuss real life hacks in chat or anywhere else. This is only a game.
							</p>                        
						</div>
                        <br>
                    </div>
                    <br>
                </div>
            </div>
        </section>
        
        <section id="screenshots">
            <div class="container">
                <h1>Screenshots Gallery</h1>
                <div id="screenshots-panel">
                    
                    <div id="screenshots-images">
                        <!-- image 1 -->
                        <input type="radio" name="screenshots" id="i1" checked>
                        <img src="images/gameplay1.PNG" id="i1">
                        <!-- image 2 -->
                        <input type="radio" name="screenshots" id="i2">
                        <img src="images/gameplay2.PNG" id="i2">
						<!-- image 3 -->
                        <input type="radio" name="screenshots" id="i3">
                        <img src="images/gameplay3.PNG" id="i3">

                    </div>
                    
                    <div id="screenshots-dots">
                        <!-- Image selector: dots -->
                        <label for="i1"></label>
                        <label for="i2"></label>
					    <label for="i3"></label>	
                    </div>
                    
                </div>
            </div>
        </section>

		<section id="login">
			<div class="container">
				<h1>Login to your account</h1>
				<div id="login-wrap">
					<form action="guest.php" method="post">
						<input type="hidden" name="h" value="login">
						<input type="text" placeholder="Email" class="icon email" name="email">
						<input type="password" placeholder="Password" class="icon pass" name="pass">
						<input type="submit" value="Login">
						<br>
						<span><a href="guest.php?h=resetpass">Lost password?</a></span>
					</form>
				</div> 
			</div>
		</section>