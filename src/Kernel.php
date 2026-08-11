<?php

declare(strict_types=1);

namespace LM\WebFramework;

use DI\ContainerBuilder;
use LM\WebFramework\Http\HttpRequestHandler;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class Kernel
{
    const CLI_ID = 'cli';

    public static function initialize(string $projectRootPath, string $language): ?ContainerInterface {
        set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
            $msg = "$errNo, $errStr in $errFile on line $errLine";
            throw new RuntimeException($msg, $errNo);
        });

        $config = new Configuration($projectRootPath, $language);

        $cb = (new ContainerBuilder());
        if (!$config->isDev()) {
            $cb->enableCompilation("$projectRootPath/var/cache");
        }
    
        $container = $cb
            ->addDefinitions([
                Configuration::class => $config,
            ])
            ->build()
        ;

        if (self::CLI_ID === php_sapi_name()) {
            return $container;
        } else {
            $container->get(HttpRequestHandler::class)->processRequest();
            return null;
        }
    }
}
