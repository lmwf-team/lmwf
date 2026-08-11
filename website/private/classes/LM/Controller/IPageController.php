<?php

namespace LM\Controller;

interface IPageController
{
	public function doGet(): void;
	public function doPost(): void;
}