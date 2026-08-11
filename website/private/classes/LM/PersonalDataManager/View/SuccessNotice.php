<?php

namespace LM\PersonalDataManager\View;

class SuccessNotice extends TemplateView
{
	private $breadcrumb;
	private $content;
	private $title;

	public function __construct(string $content, string $title)
	{
		$this->breadcrumb = array(
			'Home' => 'index.php',
			// TODO: notices don't have a location in the breadcrumb.
			// Does that mean they shouldn't be a page by themselves?
			// 'Lo' => 'index.php?page=login',
		);
		$this->content = $content;
		$this->title = $title;
	}

	public function display(): void
	{
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php $this->displayHtmlHeadContent($this->title) ?>
	</head>
	<body>
		<?php $this->displayPageHeader($this->title, $this->breadcrumb) ?>
		<main>
			<p class="success-notice-content"><?= $this->content ?></p>
		</main>
	</body>
</html>
<?php
	}
}