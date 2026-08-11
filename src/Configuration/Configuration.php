<?php

declare(strict_types=1);

namespace LM\WebFramework\Configuration;

use InvalidArgumentException;
use LM\WebFramework\Configuration\Exception\CouldNotReadFileException;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use LM\WebFramework\ErrorHandling\LogLevel;

/**
 * Creates and validates a configuration given the path to the project folder.
 *
 * @todo Add appName setting.
 */
final class Configuration
{
    public const string DIST_FN = "lmwf_app.json";
    public const string LOCAL_FN = ".lmwf_app.local.json";

    public const string APP_PATH_KEY = "appPath";
    public const string HANLDE_EXCEPTIONS = "handleExceptions";
    public const string LANGUAGE_KEY = "language";
    public const string LOG_LEVEL_KEY = "logLevel";

    public readonly HttpConf $httpConf;

    public readonly LogLevel $logLevel;

    /**
     * Gives access to the raw configuration data.
     *
     * Stored as AppObject to ensure it cannot be mutated.
     */
    public readonly AppObject $confData;

    public readonly bool $handleExceptions;
    public readonly bool $isDev;

    public readonly string $homeUrl;
    public readonly string $language;
    public readonly ?string $loggerFqcn;
    public readonly string $appRootPath;
    public readonly string $uploadRelPath;
    public readonly string $publicPath;


    /**
     * The dist file must exists. The local file might not exist, but if it
     * exists it must be readable and valid.
     *
     * @todo Add JSON_THROW_ON_ERROR everywhere, and automatically check its presence.
     * @todo Rename to "createFromFolderPath" or something like it.
     */
    public static function createFromEnvFile(
        string $confFolderPath,
        array $configData = [],
    ): self {
        if (file_exists("$confFolderPath/" . self::LOCAL_FN)) {
            $envLocal = self::readConfFile("$confFolderPath/" . self::LOCAL_FN);
            $configData += json_decode($envLocal, true, flags: JSON_THROW_ON_ERROR);
        }

        $env = self::readConfFile("$confFolderPath/" . self::DIST_FN);
        $configData += json_decode($env, true, flags: JSON_THROW_ON_ERROR);

        $configData += [
            'confFolderPath' => $confFolderPath,
        ];

        return new self(
            $configData,
        );
    }

    public static function parseLogLevel(string $logLevelStr): LogLevel
    {
        switch ($logLevelStr) {
            case "NOTICE":
                return LogLevel::NOTICE;
        }
        throw new InvalidArgumentException("Log level \"{$logLevelStr}\" specified in configuration is unknown.");
    }

    /**
     * @todo Could go in a separate service dedicated to reading files.
     */
    public static function readConfFile(string $filePath): string
    {
        $fileContent = file_get_contents($filePath);
        if (false === $fileContent) {
            throw new CouldNotReadFileException($filePath);
        }
        return $fileContent;
    }

    /**
     * @todo Create model for configuration, and check it is valid? (Would make testing harder.)
     * @todo Accept an array and create a model from it?
     */
    public function __construct(array $confData)
    {
        $this->handleExceptions = $confData['handleExceptions'];
        $this->isDev = $confData['isDev'];

        $this->homeUrl = $confData['homeUrl'];
        $this->language = $confData['language'];
        $this->loggerFqcn = $confData['loggerFqcn'];
        $this->appRootPath = $confData['appRootPath'];
        $this->uploadRelPath = $confData['uploadRelPath'];
        $this->publicPath = $confData['publicPath'];

        $this->logLevel = self::parseLogLevel($confData[self::LOG_LEVEL_KEY]);

        $this->httpConf = new HttpConf(
            (new RouteDefParser())->parse($confData['rootRoute']),
            $this->handleExceptions,
            implode(' ', $confData['cspDefaultSources']),
            implode(' ', $confData['cspFontSources']),
            implode(' ', $confData['cspObjectSources']),
            implode(' ', $confData['cspStyleSources']),
            $confData['routeError404ControllerFQCN'],
            $confData['routeErrorAlreadyLoggedInControllerFQCN'],
            $confData['routeErrorNotLoggedInControllerFQCN'],
            $confData['routeErrorMethodNotSupportedFQCN'],
            $confData['serverErrorControllerFQCN'],
        );

        $this->confData = CollectionFactory::createDeepAppObject($confData);
    }

    public function getBoolSetting(string $key): bool
    {
        return $this->confData[$key];
    }

    public function getNullableSetting(string $key): ?string
    {
        return $this->confData[$key];
    }

    public function getPathOfUploadedFiles(): string
    {
        return $this->appRootPath . '/' . $this->uploadRelPath;
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

    public function hasSetting(string $key): bool
    {
        return key_exists($key, $this->confData);
    }
}
