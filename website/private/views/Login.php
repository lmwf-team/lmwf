<?php // check html semantic, best practices, completion, and accessibility

require_once(private_root().'views/I_Controller.php');

class Login implements I_Controller
{
    public function get(): void
    {
    ?>
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
    <?php
    }

    public function post(): void
    {
        // TODO: check doctrine and database mapping

        require_once(lib_root().'c_database/Database.php');
        require_once(lib_root().'f_errorpage/f_errorpage.php');

        $db = Database::getConnection();

        $username = $_POST['username'];
        $password = $_POST['password'];
        $request = Database::query( 'SELECT password FROM person WHERE id = ?;', array( $username) );
        if ( $request->rowCount() === 0 | $request->fetch()['password'] !== $password ) {
            displayErrorPage('');
        }
        else {
            echo 'correct';
            setUsername($username);
        }
    }
}