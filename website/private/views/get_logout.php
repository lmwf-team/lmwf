<?php
require_once(private_root().'lib/f_errorpage/f_errorpage.php');
if (!isConnected()) :
    displayErrorPage('Permission Denied', 'You need to be logged in to access this page.');
else:
?>
<?php logOut(); ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo translate('Successful Logout') ?></title>
    </head>
    <body>
    </body>
</html>ç
<?php endif ?>