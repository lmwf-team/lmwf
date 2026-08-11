<?php

require_once(private_root().'views/I_Controller.php');

class Logout implements I_Controller
{
    function get(): void
    {
        require_once(private_root().'lib/f_errorpage/f_errorpage.php');
        if (!isConnected()) :
            displayErrorPage('Permission Denied', 'You need to be logged in to access this page.');
        else:
        logOut();
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title><?php echo translate('Successful Logout') ?></title>
            </head>
            <body>
            </body>
        </html>
        <?php endif;
    }

    function post(): void
    {

    }
}