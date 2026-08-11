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