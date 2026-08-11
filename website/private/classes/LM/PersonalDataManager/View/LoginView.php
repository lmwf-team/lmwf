<?php

namespace LM\PersonalDataManager\View;

use LM\WebFramework\View\IView;

class LoginView implements IView
{
	public function display(): void
	{
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Login</title>
	</head>
	<body>
		<header>
			<h1>Login</h1>
			<nav>
				<a href="index.php">Home</a> / Login
			</nav>
		</header>
		<main>
			<p>Log in!</p>
		</main>
	</body>
</html>
<?php
	}
}