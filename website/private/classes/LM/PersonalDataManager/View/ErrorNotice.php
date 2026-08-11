<?php

namespace LM\PersonalDataManager\View;

// TODO: this class could be merged with SucessNotice
class ErrorNotice extends TemplateView
{
	private $breadcrumb;
	private $content;
	private $title;

	public function __construct(string $content, string $title)
	{
		$this->breadcrumb = array(
			'Home' => 'index.php',
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
			<p class="error-notice-content"><?= $this->content ?></p>
		</main>
	</body>
</html>
<?php
	}
}