<?php

namespace LM\WebFramework\Controller;

use LM\WebFramework\Request\IPostRequest;
use LM\WebFramework\Request\IRequest;

interface IPageController
{
	public function doGet(IRequest $request): void;
	public function doPost(IPostRequest $request): void;
}