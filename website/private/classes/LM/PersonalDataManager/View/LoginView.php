<?php

namespace LM\PersonalDataManager\View;

use \LM\WebFramework\Form\FormReader;

class LoginView extends TemplateView
{
	private $breadcrumb;
	private $formReader;

	public function __construct(FormReader $formReader)
	{
		$this->breadcrumb = array(
			'Home' => 'index.php',
			'Log In' => 'index.php?page=login',
		);
		$this->formReader = $formReader;
	}

	public function display(): void
	{
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php $this->displayHtmlHeadContent('Login') ?>
	</head>
	<body>
		<?php $this->displayPageHeader('Login', $this->breadcrumb) ?>
		<main>
			<div class="main-content-container">
				<form action="index.php?page=login" method="post">
					<dl class="no-margin-top">
						<dt>
							<label for="username">
								Username: 
							</label>
						</dt>
						<dd>
							<?php if ($this->formReader->hasErrorFor('username')) : ?>
							<p><?= $this->formReader->getError('username') ?></p>
							<?php endif ?>
							<input autocomplete="username" autofocus id="username" inputmode="verbatim" maxlength="<?= PDM_USERNAME_MAX_LENGTH ?>" name="username" required type="text">
						</dd>
						<dt>
							<label for="password">
								Password: 
							</label>
						</dt>
						<dd>
							<?php if ($this->formReader->hasErrorFor('password')) : ?>
							<p><?= $this->formReader->getError('password') ?></p>
							<?php endif ?>
							<input autocomplete="current-password" id="password" inputmode="verbatim" maxlength="<?= PDM_PASSWORD_MAX_LENGTH ?>" name="password" required type="password">
						</dd>
					</dl>
					<input type="submit" value="Log in">
				</form>
			</div>
		</main>
	</body>
</html>
<?php
	}
}