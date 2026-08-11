<?php

namespace LM\WebFramework\Routing;

class UriBuilder implements IUriBuilder
{
    private $config;

    public function __construct(string $config_filename)
    {
        $config_file = file_get_contents($config_filename);
        $this->config = json_decode($config_file, true);
    }

    public function getUri(string $resource_name): string
    {
        return $this->getProtocol().$_SERVER['SERVER_NAME'].'/'.$this->config['prefix'].$resource_name;
    }

    private function getProtocol(): string
    {
        return $this->isSecure() ? 'https://' : 'http://';
    }

    /**
     * @link https://stackoverflow.com/a/2886224/7089212
     */
    private function isSecure()
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }
}