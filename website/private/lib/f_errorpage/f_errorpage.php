<?php

function displayErrorPage(string $title, string $message)
{
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <title><?php echo translate($title) ?></title>
        </head>
        <body>
            <h1><?php echo translate($title) ?></h1>
            <p><?php echo translate($message) ?></p>
        </body>
    </html>
    <?php
}