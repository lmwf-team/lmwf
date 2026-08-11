<?php

declare(strict_types=1);

namespace LM\WebFramework;

use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;

final class Configuration
{
    private AppObject $configData;

    private string $configFolderPath;

    private string $language;

    public function __construct(string $configFolderPath, string $language)
    {
        $this->configFolderPath = $configFolderPath;
        $this->language = $language;

        $env = file_get_contents("$configFolderPath/.env.json");
        $envLocal = file_get_contents("$configFolderPath/.env.json.local");
        $configData = false !== $envLocal ? json_decode($envLocal, true) : [];
        $configData += false !== $env ? json_decode($env, true) : [];
        $this->configData = (new CollectionFactory())->createDeepAppObject($configData);
    }

    public function getBoolSetting(string $key): bool
    {
        return $this->configData[$key];
    }
    
    public function getConfigAppData(): AppObject
    {
        return $this->configData;
    }

    /**
     * @return string List of valid CSP origins
     */
    public function getCSPDefaultSources(): string
    {
        return $this->configData->getAppList('cspDefaultSources')->implode(' ');
    }

    /**
     * @return string List of valid CSP font origins
     */
    public function getCSPFontSources(): string
    {
        return $this->configData->getAppList('cspFontSources')->implode(' ');
    }

    /**
     * @return string List of valid CSP object origins
     */
    public function getCSPObjectSources(): string
    {
        return $this->configData->getAppList('cspObjectSources')->implode(' ');
    }

    /**
     * @return string List of valid CSP style origins
     */
    public function getCSPStyleSources(): string
    {
        return $this->configData->getAppList('cspStyleSources')->implode(' ');
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

    public function getLoggingPrefix(): string
    {
        return $this->getSetting('loggingPrefix');
    }

    public function getPathOfAppDirectory(): string
    {
        return $this->configFolderPath;
    }

    public function getPathOfUploadedFiles(): string
    {
        return $this->configFolderPath . '/' . $this->configData['pathOfUploadedFiles'];
    }

    public function getPublicUrl(): string
    {
        return $this->getSetting('publicUrl');
    }

    public function getRoutes(): AppObject
    {
        return $this->configData['routes'];
    }

    public function getServerErrorControllerFQCN(): string
    {
        return $this->getSetting('serverErrorControllerFQCN');
    }

    public function getSetting(string $key): string
    {
        return $this->configData[$key];
    }

    public function isDev(): bool
    {
        return $this->getBoolSetting('dev');
    }
}