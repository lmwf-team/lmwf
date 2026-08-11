<?php

require_once(private_root().'views/I_Controller.php');

class Index implements I_Controller
{
    public function get(): void
    {
    ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Ça marche !</title>
            </head>
            <body>
                <?php if (isConnected()) : ?>
                <h1>Hello, <?php echo getUsername() ?></h1>
                <?php endif ?>
            </body>
        </html>
    <?php
    }

    public function post(): void
    {

    }
}