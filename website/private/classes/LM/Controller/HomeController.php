<?php

namespace LM\Controller;

class HomeController implements IPageController
{
	public function doGet(): void {
		echo 'get request';
	}

	public function doPost(): void {
		echo 'post request';
	}
}