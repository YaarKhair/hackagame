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