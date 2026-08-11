<?php

namespace Lm\PersonalDataManager\Controller;

use LM\PersonalDataManager\View\TestSpView;
use LM\WebFramework\Controller\IPageController;

class TestSpController implements IPageController
{
	public function doGet(): void {
		define('DOORS_NO', 3);
		define('NO_OF_GAMES', 10000);
		$noOfGoodGuesses = 0;

		for ($i = 0; $i < NO_OF_GAMES; $i++) {
			$goodDoorNo = mt_rand(1, DOORS_NO);

			$choice = mt_rand(1, DOORS_NO);

			$badDoorNo = null;
			while ($badDoorNo === null || $badDoorNo === $goodDoorNo || $badDoorNo === $choice) {
				$badDoorNo = mt_rand(1, DOORS_NO);
			}
			
			$newChoice = null;
			while ($newChoice === null || $newChoice === $choice || $newChoice === $badDoorNo) {
				$newChoice = mt_rand(1, DOORS_NO);
			}

			if ($newChoice === $goodDoorNo) {
				$noOfGoodGuesses++;
			}
		}


		$view = new TestSpView($noOfGoodGuesses / NO_OF_GAMES);
		$view->display();
	}

	public function doPost(): void {
		echo 'post request';
	}
}