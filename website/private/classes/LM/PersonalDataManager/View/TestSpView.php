<?php

namespace LM\PersonalDataManager\View;

use LM\WebFramework\View\IView;

class TestSpView implements IView
{
	private $percentage;

	public function __construct(float $percentage) {
		$this->percentage = $percentage;
	}

	public function display(): void
	{
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Test</title>
	</head>
	<body>
		<header>
			<h1>Test</h1>
		</header>
		<main>
			<p><?= $this->percentage ?>%</p>
		</main>
	</body>
</html>
<?php
	}
}