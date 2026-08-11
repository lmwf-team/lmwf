<?php

namespace LM;

interface IPageController
{
	public function doGet(): void;
	public function doPost(): void;
}