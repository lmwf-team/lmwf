<?php

namespace LM\PersonalDataManager\Controller;

use LM\Database\DatabaseConnection;
use LM\PersonalDataManager\View\LoginView;
use LM\WebFramework\Controller\IPageController;
use LM\WebFramework\Form\FormReader;

class LoginController implements IPageController
{
	public function doGet(): void {
		$view = new LoginView(new FormReader);
		$view->display();
	}

	public function doPost(array $postData): void {
		// 1. check the form
		$formReader = new FormReader($postData);
		$formReader->readUsername('username');
		$formReader->readPassword('password');

		if ($formReader->hasErrors()) {
			$view = new LoginView($formReader);
			$view->display();
		} else {
			echo 'post request';
		}
		// 2. connect to the database
		// 3. execute statement and get returned query
		// 4. analyse query
		// 5. take approppritae action
	}
}