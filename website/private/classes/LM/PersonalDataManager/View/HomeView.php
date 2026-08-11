<?php

namespace LM\PersonalDataManager\View;

class HomeView extends TemplateView
{
	private $breadcrumb;

	public function __construct()
	{
		$this->breadcrumb = array(
			'Home' => 'index.php',
		);
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
		<main>
			<div class="main-black-transparent-background">
				<p>Personal Data Manager is the essential tool to help you manage your life! <a href="index.php?page=login">Log in!</a></p>
			</div>
		</main>
	</body>
</html>
<?php
	}
}