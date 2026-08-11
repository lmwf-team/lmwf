<?php

namespace LM\PersonalDataManager\Controller;

use LM\Database\DatabaseConnection;
use LM\PersonalDataManager\View\LoginView;
use LM\WebFramework\Controller\IPageController;
use LM\WebFramework\Form\FormReader;

class LoginController implements IPageController
{
	public function doGet(): void {
		$view = new LoginView(new FormReader(array()));
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
			$pdo = DatabaseConnection::getInstance()->getPdo();
			// 3. execute statement and get returned query
			$query = 'SELECT COUNT(id) FROM `shift-two_pdm`.person WHERE id = :username AND password = :password';
			$request = $pdo->prepare($query);
			$request->execute(array('username' => $formReader->getUsername('username')->getString(), 'password' => $formReader->getPassword('password')->getString()));
			if ("1" === $request->fetchAll()[0]["COUNT(id)"]) {
				echo 'connected';
			} else {
				echo 'wrong';
			}
		}
		// 2. connect to the database

		// 4. analyse query
		// 5. take approppritae action
	}
}