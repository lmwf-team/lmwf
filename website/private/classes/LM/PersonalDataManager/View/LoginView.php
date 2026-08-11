<?php

namespace LM\PersonalDataManager\View;

class LoginView extends TemplateView
{
	public function display(): void
	{
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php $this->displayHtmlHeadContent('Login') ?>
	</head>
	<body>
		<?php $this->displayPageHeader('Login') ?>
		<main>
			<div class="main-black-transparent-background">
				<form>
					<dl class="no-margin-top">
						<dt>
							<label for="username">
								Username: 
							</label>
						</dt>
						<dd>
							<input autocomplete="username" autofocus id="username" inputmode="verbatim" maxlength="<?= PDM_USERNAME_MAX_LENGTH ?>" name="username" required type="text">
						</dd>
						<dt>
							<label for="password">
								Password: 
							</label>
						</dt>
						<dd>
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