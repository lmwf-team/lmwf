<?php

namespace LM\WebFramework;

class Configuration
{
    private array $env;

    public function __construct(string $configFolderPath) {
        $env = file_get_contents("$configFolderPath/.env.json");
        $envLocal = file_get_contents("$configFolderPath/.env.local.json");
        $this->env = (false !== $envLocal ? json_decode($envLocal, true) : []) +
            (false !== $env ? json_decode($env, true) : [])
        ;
    }

    public function getBoolSetting(string $key): bool {
        return $this->env[$key];
    }

    public function getErrorLoggedInControllerFQCN(): string {
        return $this->getSetting('route_error_logged_in_controller_fqcn');
    }

    public function getErrorNotFoundControllerFQCN(): string {
        return $this->getSetting('route_error_404_controller_fqcn');
    }

    public function getErrorNotLoggedInControllerFQCN(): string {
        return $this->getSetting('route_error_not_logged_in_controller_fqcn');
    }

    public function getHomeUrl(): string {
        return $this->getSetting('homeUrl');
    }

    public function getPublicUrl(): string {
        return $this->getSetting('publicUrl');
    }

    public function getRoutes(): array {
        return $this->env['routes'];
    }

    public function getSetting(string $key): string {
        return $this->env[$key];
    }

    public function getUploadedFileFolder(): string {
        return $this->env['uploadedFilesFolder'];
    }

    public function isDev(): bool {
        return $this->getBoolSetting('dev');
    }
}