<?php

namespace LM\PersonalDataManager\Controller;

use LM\PersonalDataManager\View\HomeView;
use LM\WebFramework\Controller\IPageController;

class HomeController implements IPageController
{
	public function doGet(): void {
		$view = new HomeView;
		$view->display();
	}

	public function doPost(array $postData): void {
		echo 'post request';
	}
}