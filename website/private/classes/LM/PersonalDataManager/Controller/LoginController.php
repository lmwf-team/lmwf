<?php

namespace LM\PersonalDataManager\Controller;

use LM\Database\DatabaseConnection;
use LM\PersonalDataManager\View\LoginView;
use LM\PersonalDataManager\View\SuccessNotice;
use LM\WebFramework\Controller\IPageController;
use LM\WebFramework\Identification\UserIdentification;
use LM\WebFramework\Form\FormReader;

class LoginController implements IPageController
{
	private $userIdentification;

	public function __construct()
	{
		$this->userIdentification = new UserIdentification;
	}

	public function doGet(): void
	{
		if ($this->userIdentification->isLoggedIn()) {
			$view = new ErrorNotice('You cannot access the login page while being logged in.', 'Access Denied');
			$view->display();
		} else {
			$view = new LoginView(new FormReader(array()));
			$view->display();
		}
	}

	public function doPost(array $postData): void
	{
		if ($this->userIdentification->isLoggedIn()) {
			$view = new ErrorNotice('You cannot access the login page while being logged in.', 'Access Denied');
			$view->display();
		} else {
			$formReader = new FormReader($postData);
			$formReader->readUsername('username');
			$formReader->readPassword('password');

			if ($formReader->hasErrors()) {
				$view = new LoginView($formReader);
				$view->display();
			} else {
				$pdo = DatabaseConnection::getInstance()->getPdo();
				// TODO: should it check that the user is not already logged in?
				$query = 'SELECT COUNT(id) FROM `shift-two_pdm`.person WHERE id = :username AND password = :password';
				$request = $pdo->prepare($query);
				$request->execute(array('username' => $formReader->getUsername('username')->getString(), 'password' => $formReader->getPassword('password')->getString()));
				if ("1" === $request->fetchAll()[0]["COUNT(id)"]) {
					$this->userIdentification->logUserIn($formReader->getUsername('username'));
					$view = new SuccessNotice('You logged in successfully', 'Successful Login');
					$view->display();
				} else {
					$formReader->addGeneralError('Incorrect Credentials.');
					$view = new LoginView($formReader);
					$view->display();
				}
			}
		}
	}
}