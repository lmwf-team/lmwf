<?php

declare(strict_types=1);

namespace LM\WebFramework\Configuration;

use LM\WebFramework\Configuration\Exception\SettingNotFoundException;
use LM\WebFramework\DataStructures\AppObject;

final class Configuration
{
    /**
     * @todo Create model for configuration, and check it is valid.
     * @todo Accept an array and create a model from it?
     */
    public function __construct(
        private AppObject $configData,
        private string $configFolderPath,
        private string $language,
    ) {
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

    /**
     * @todo Sghould implement PSR LoggerInterface.
     */
    public function getLoggerFqcn(): ?string
    {
        return $this->getNullableSetting('loggerFqcn');
    }

    public function getNullableSetting(string $key): ?string
    {
        return $this->configData[$key];
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
        return $this->configData->getAppObject('rootRoute');
    }

    public function getServerErrorControllerFQCN(): string
    {
        return $this->getSetting('serverErrorControllerFQCN');
    }

    /**
     * @todo Add test.
     */
    public function getSetting(string $key): string
    {
        $path = explode('.', $key);
        $data = $this->configData;
        foreach ($path as $key) {
            $data = $data[$key];
        }
        return $data;
    }

    public function isDev(): bool
    {
        return $this->getBoolSetting('dev');
    }
}
