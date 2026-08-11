<?php

namespace LM\WebFramework\Controller;

interface IPageController
{
	public function doGet(): void;
	public function doPost(): void;
}