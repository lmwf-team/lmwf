<?php

declare(strict_types=1);

namespace LM\WebFramework;

final class Configuration
{
    private array $env;

    private string $configFolderPath;

    private string $language;

    public function __construct(string $configFolderPath, string $language)
    {
        $this->configFolderPath = $configFolderPath;
        $this->language = $language;

        $env = file_get_contents("$configFolderPath/.env.json");
        $envLocal = file_get_contents("$configFolderPath/.env.local.json");
        $this->env = (false !== $envLocal ? json_decode($envLocal, true) : []) +
            (false !== $env ? json_decode($env, true) : [])
        ;
    }

    public function getBoolSetting(string $key): bool
    {
        return $this->env[$key];
    }

    public function getErrorLoggedInControllerFQCN(): string
    {
        return $this->getSetting('routeErrorAlreadyLoggedInControllerFQCN');
    }

    public function getErrorNotFoundControllerFQCN(): string
    {
        return $this->getSetting('routeError404ControllerFQCN');
    }

    public function getErrorNotLoggedInControllerFQCN(): string
    {
        return $this->getSetting('routeErrorNotLoggedInControllerFQCN');
    }

    public function getHomeUrl(): string
    {
        return $this->getSetting('homeUrl');
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getPathOfProjectDirectory(): string
    {
        return $this->configFolderPath;
    }

    public function getPathOfUploadedFiles(): string
    {
        return $this->env['pathOfUploadedFiles'];
    }

    public function getPublicUrl(): string
    {
        return $this->getSetting('publicUrl');
    }

    public function getRoutes(): array
    {
        return $this->env['routes'];
    }

    public function getServerErrorControllerFQCN(): string {
        return $this->getSetting('serverErrorControllerFQCN');
    }

    public function getSetting(string $key): string
    {
        return $this->env[$key];
    }

    public function isDev(): bool
    {
        return $this->getBoolSetting('dev');
    }
}