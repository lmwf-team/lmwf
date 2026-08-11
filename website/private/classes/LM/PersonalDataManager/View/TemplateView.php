<?php

namespace LM\PersonalDataManager\View;

use LM\WebFramework\View\IView;

abstract class TemplateView implements IView
{
	public function displayHtmlHeadContent(string $pageTitle): void
	{
?>
<meta charset="utf-8">
<title><?= $pageTitle ?></title>
<link href="style.css" rel="stylesheet" title="Default Style" type="text/css">
<?php
	}

	public function displayPageHeader(string $pageHeader): void
	{
?>
	<header>
		<h1 class="no-margin-bottom">
			<div class="black-background">
				<?= $pageHeader ?>
			</div>
		</h1>
		<div class="main-black-transparent-background">
			<nav>
				<ol class="breadcrumb">
					<li>
						<a href="index.php">
							Home
						</a>
					</li>
					<li>
						<a href="index.php?page=login">
							Login
						</a>
					</li>
				</ol>
			</nav>
		</div>
	</header>
<?php
	}
}