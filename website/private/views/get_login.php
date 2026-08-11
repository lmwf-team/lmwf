<?php // check html semantic, best practices, completion, and accessibility ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo translate('Login') ?></title>
    </head>
    <body>
        <form method="POST" action="#">
            <label for="username">
                <input id="username" name="username" />
            </label>
            <label for="password">
                <input id="password" name="password" type="password" />
            </label>
            <button type="submit"><?php echo translate('Login') ?></button>
        </form>
    </body>
</html>