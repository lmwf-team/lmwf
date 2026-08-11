<?php

namespace LM\Controller;

use LM\View\HomeView;

class HomeController implements IPageController
{
	public function doGet(): void {
		$view = new HomeView;
		$view->display();
	}

	public function doPost(): void {
		echo 'post request';
	}
}