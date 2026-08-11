<?php

namespace LM\WebFramework;

use DI\ContainerBuilder;
use LM\WebFramework\Http\HttpRequestHandler;
use Psr\Container\ContainerInterface;

class Kernel
{
    const CLI_ID = 'cli';

    public static function initialize(string $projectRootPath): ?ContainerInterface {
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
