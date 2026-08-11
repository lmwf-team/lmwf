<?php

namespace LM;

class HomeController implements IPageController
{
	public function doGet(): void {
		echo 'get request';
	}

	public function doPost(): void {
		echo 'post request';
	}
}