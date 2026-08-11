<?php

namespace LM\PersonalDataManager\View;

use LM\WebFramework\Identification\UserIdentification;

class HomeView extends TemplateView
{
	private $breadcrumb;
	private $userIdentification;

	public function __construct()
	{
		$this->breadcrumb = array(
			'Home' => 'index.php',
		);
		$this->userIdentification = new UserIdentification;
	}
	public function display(): void
	{
?>
<!DOCTYPE html>
<html>
	<head>
		<?php $this->displayHtmlHeadContent('Login') ?>
	</head>
	<body>
		<?php $this->displayPageHeader('Login', $this->breadcrumb) ?>
		<?php if ($this->userIdentification->isLoggedIn()) : ?>
		<aside class="first-level-container"><p>Hello, <?= $this->userIdentification->getUsername()->getString() ?>.</p></aside>
		<?php endif ?>
		<main>
			<div class="main-content-container">
				<p>Personal Data Manager is the essential tool to help you manage your life! <a href="index.php?page=login">Log in!</a></p>
			</div>
		</main>
	</body>
</html>
<?php
	}
}