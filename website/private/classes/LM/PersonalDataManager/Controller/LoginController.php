<?php

namespace LM\PersonalDataManager\Controller;

use LM\PersonalDataManager\View\LoginView;
use LM\WebFramework\Controller\IPageController;

class LoginController implements IPageController
{
	public function doGet(): void {
		$view = new LoginView;
		$view->display();
	}

	public function doPost(): void {
		echo 'post request';
	}
}