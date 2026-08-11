# Personal Data Manager

## General description

This is the source code of a website and of a very simple web framework it uses
which was made specifically for it because of a very limited space constraint.

## LM Web Framework

The web framework used, LM Web Framework, was created specifically for this
project and is very simple. It is developed along the website, at least for now,
and is part of its Git repository. It is designed to be very small in size and
simple to use.

In regards to server configuration, the only requirement is to prevent public
access to the private folder at the root of the framework folder.

The framework files are organised as below:

 * private/: contains the files which must not be accessible by visitors
 directly.
     * classes/: contains the classes of the project
         * LM/
             * PersonalDataManager/: classes specifically associated with the
         Personal Data Manager project
                 * Controller/: the request controllers implementing 
                 LM\WebFramework\Controller\IController
                 * View/: the views implemeting LM\WebFramework\View\IView
             * WebFramework/: classes part of the framework
                 * Controller/: the IController interface
                 * View/: the IView interface
                 * Router.php: the Router class
     * functions/: contains the functions of the project (to be removed)
 * public/:
    * index.php: the main controller of the framework. It's a script and its 
    specific name and path makes it able to handle all requests related to the
    website. It defines constants, includes and registers the auto-loader, and
    calls the right method on the controller (retrieved via a Router object).

A number of modifications could be made in the future.


 * First, "Controller" could be later changed for "PageController", as it is less
ambiguous and avoids confusion between a page controller and the main
controller.
 * The Router class could be moved to a specific namespace and implement an
interface, e.g. IRouter.
 * The main controller, index.php, poses a number of issues. It does not have
 a namespace yet it is part of the framework. The URLs of the requests it can
 handle show get parameters in their native form, which is not desirable.
 Ultimately, server configuration will be necessary to ensure URLs are displayed
 properly and that the main controller can be located and named in a way more
 consistent with the rest of the framework's files.
 * The functions folder could be removed, in order to make the framework (and 
 the website) 100% OOP, except the main controller. But this would require
 finding a way to register an object's method as an autoloader.
 * PHP documentation needs to be applied and package names need to be
 determined.

All the code, both of the Personal Data Manager project and of the 
LM Web Framework project meet the PSR-1, PSR-2 and PSR-4.
