<?php

declare(strict_types=1);

namespace LM\WebFramework\Configuration;

use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;

/**
 * @todo Add appName setting.
 */
final class Configuration
{
    public const string APP_PATH_KEY = "appPath";
    public const string HANLDE_EXCEPTIONS = "handleExceptions";
    public const string LANGUAGE_KEY = "language";

    private AppObject $confData;

    /**
     * @todo Add JSON_THROW_ON_ERROR everywhere, and automatically check its presence.
     */
    public static function createFromEnvFile(
        string $confFolderPath,
        string $language,
        array $configData = [],
    ): self {
        $env = file_get_contents("$confFolderPath/.env.json");
        $envLocal = file_get_contents("$confFolderPath/.env.json.local");
        $configData += false !== $envLocal ? json_decode($envLocal, true, flags: JSON_THROW_ON_ERROR) : [];
        $configData += false !== $env ? json_decode($env, true, flags: JSON_THROW_ON_ERROR) : [];
        $configData += [
            'language' => $language,
            'confFolderPath' => $confFolderPath,
        ];

        return new self(
            $configData,
        );
    }

    /**
     * @todo Create model for configuration, and check it is valid? (Would make testing harder.)
     * @todo Accept an array and create a model from it?
     */
    public function __construct(array $confData, bool $handleExceptions = true) {
        $confData += [
            self::HANLDE_EXCEPTIONS => $handleExceptions,
        ];
        $this->confData = CollectionFactory::createDeepAppObject($confData);
    }

    public function getBoolSetting(string $key): bool
    {
        return $this->confData[$key];
    }

    public function getConfigAppData(): AppObject
    {
        return $this->confData;
    }

    /**
     * @return string List of valid CSP origins
     */
    public function getCSPDefaultSources(): string
    {
        return $this->confData->getAppList('cspDefaultSources')->implode(' ');
    }

    /**
     * @return string List of valid CSP font origins
     */
    public function getCSPFontSources(): string
    {
        return $this->confData->getAppList('cspFontSources')->implode(' ');
    }

    /**
     * @return string List of valid CSP object origins
     */
    public function getCSPObjectSources(): string
    {
        return $this->confData->getAppList('cspObjectSources')->implode(' ');
    }

    /**
     * @return string List of valid CSP style origins
     */
    public function getCSPStyleSources(): string
    {
        return $this->confData->getAppList('cspStyleSources')->implode(' ');
    }

    public function getErrorLoggedInControllerFQCN(): string
    {
        return $this->getSetting('routeErrorAlreadyLoggedInControllerFQCN');
    }

    public function getErrorMethodNotSupportedFQCN(): string
    {
        return $this->getSetting('routeMethodNotSupportedFQCN');
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
        return $this->confData[self::LANGUAGE_KEY];
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
        return $this->confData[$key];
    }

    public function getPathOfAppDirectory(): string
    {
        return $this->confData[self::APP_PATH_KEY];
    }

    public function getPathOfUploadedFiles(): string
    {
        return $this->confData[self::APP_PATH_KEY] . '/' . $this->confData['pathOfUploadedFiles'];
    }

    public function getPublicUrl(): string
    {
        return $this->getSetting('publicUrl');
    }

    /**
     * @todo Rename to getMainRoute or getRootRoute.
     */
    public function getRoutes(): AppObject
    {
        return $this->confData->getAppObject('rootRoute');
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
        $data = $this->confData;
        foreach ($path as $key) {
            $data = $data[$key];
        }
        return $data;
    }

    public function handleExceptions(): bool
    {
        return $this->confData->getBool(self::HANLDE_EXCEPTIONS);
    }

    public function hasSetting(string $key): bool
    {
        return $this->confData->hasProperty($key);
    }

    public function isDev(): bool
    {
        return $this->getBoolSetting('dev');
    }
}
