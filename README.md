# lmwf

lmwf is a collection of classes to provide a very simple web framework. It is 
designed to be simple to use and very small in size.

In regards to server configuration, the only requirement is to have one rewriting rule:
~~~~
RewriteEngine on
RewriteRule \A(.*)\Z /index.php?page=$1
~~~~

To get started, you first need to install the project as a Composer dependency.
Your composer.json file should be like:
~~~~
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/lmwf-team/lmwf"
        }
    ],
    "require": {
        "lmwf-team/lmwf": "dev-master"
    },
    "autoload": {
        "psr-4": {
            "LM\\Portfolio\\": "classes/LM/Portfolio/"
        }
    }
}
~~~~

You then create a file index.php at the root of the website in a similar fashion to this:
~~~~
<?php

require_once 'vendor/autoload.php';

use LM\WebFramework\Controller\MainController;
use LM\WebFramework\Routing\CustomizableRouter;
use LM\Portfolio\AmpHomeController;
use LM\Portfolio\HomeController;
use LM\Portfolio\VersionController;
use LM\Portfolio\NoPageFoundController;

/**
 * The key is the regex of the uri, the value is a controller.
 * The only condition for the controller is to be an instance of a class implementing IPageController.
 */
$routes_config = array(
    '/\A\/\Z/' => new HomeController(),
    '/\A\/amp\Z/' => new AmpHomeController(),
    '/\A\/([a-z]+\/)*[a-z0-9]+\.[0-9]+(\.[a-z]+)*\Z/' => new VersionController(),
    '/\A.*\Z/' => new NoPageFoundController(),
);

$router = new CustomizableRouter($routes_config);
$main_controller = new MainController();
$main_controller->processRequest($router);
~~~~

See the [source code of my portfolio website](https://github.com/lmwf-team/portfolio) to see a practical example.

A number of modifications could be made in the future.
 * PHP documentation needs to be applied and package names need to be
 determined.
 * Strict typing.
 * Implement view inheritance system?
 * Dependency injection

All the classes use the PSR-1, PSR-2 and PSR-4 guidelines.