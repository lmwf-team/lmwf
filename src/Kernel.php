<?php

namespace LM\WebFramework;

use DI\ContainerBuilder;
use LM\WebFramework\Http\HttpRequestHandler;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Kernel
{
    const CLI_ID = 'cli';

    public static function initialize(string $projectRootPath): ?ContainerInterface {
        set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
            $msg = "$errNo, $errStr in $errFile on line $errLine";
            throw new RuntimeException($msg, $errNo);
        });
    
        $container = (new ContainerBuilder())
            ->addDefinitions([
                Configuration::class => new Configuration($projectRootPath),
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
