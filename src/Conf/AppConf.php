<?php

declare(strict_types=1);

namespace LMWF\Conf;

use LMWF\DataStructures\AppObject;
use LMWF\DataStructures\Factory\CollectionFactory;
use LMWF\DataStructures\ImgFormat;

/**
 * Creates and validates a configuration given the path to the project folder.
 *
 * It creates a configuration file that merges the distributed configuration
 * file, lmwf_app.json, with the local file, .lmwf_app.local.json. If the two
 * define the same key, the latter overrides the former. *Note that the entire
 * value for the key is overriden, even if the value is a dictionnary.*
 *
 * @todo Add appName setting.
 */
final readonly class AppConf
{
    public const string DIST_FN = "lmwf_app.json";
    public const string LOCAL_FN = ".lmwf_app.local.json";

    public const string APP_PATH_KEY = "appPath";
    public const string HANLDE_EXCEPTIONS = "handleExceptions";
    public const string LANGUAGE_KEY = "language";

    public HttpConf $httpConf;

    /**
     * Gives access to the raw configuration data.
     *
     * Stored as AppObject to ensure it cannot be mutated.
     */
    public AppObject $confData;

    public bool $handleExceptions;
    public bool $isDev;

    public string $homeUrl;
    public string $language;
    public string $appRootPath;
    public string $uploadRelPath;
    public string $publicRelPath;

    /**
     * @var array<string, ImgFormat>
     */
    public array $thumbnailFormats;

    /**
     * The dist file must exists. The local file might not exist, but if it
     * exists it must be readable and valid.
     *
     * @todo Add JSON_THROW_ON_ERROR everywhere, and automatically check its presence.
     * @todo Rename to "createFromFolderPath" or something like it.
     *
     * @param array<string, mixed> $confData
     */
    public static function createFromEnvFile(
        string $confFolderPath,
        array $confData = [],
    ): self {
        $localConfPath = "$confFolderPath/" . self::LOCAL_FN;
        if (file_exists($localConfPath)) {
            $confData += CollectionFactory::fromJson($localConfPath);
        }

        $confData += CollectionFactory::fromJson("$confFolderPath/" . self::DIST_FN);

        $confData += [
            'confFolderPath' => $confFolderPath,
        ];

        return new self(CollectionFactory::createDeepAppObject($confData));
    }

    /**
     * The path is the absolute path on the server file system. The relative
     * path is the path on the file system relative to the app root.
     *
     * @todo Create model for configuration, and check it is valid? (Would make testing harder.)
     * @todo Accept an array and create a model from it?
     *
     * @param AppObject<mixed> $confParams
     */
    public function __construct(AppObject $confParams)
    {
        $this->handleExceptions = $confParams->getBool('handleExceptions');
        $this->isDev = $confParams->getBool('isDev');

        $this->homeUrl = $confParams->getString('homeUrl');
        $this->language = $confParams->getString('language');
        $this->appRootPath = $confParams->getString('appRootPath');
        $this->uploadRelPath = $confParams->getString('uploadRelPath');
        $this->publicRelPath = $confParams->getString('publicRelPath');

        $this->thumbnailFormats = $confParams
            ->getAppObject('thumbnailFormats')
            ->map(fn ($formatConf) => new ImgFormat(
                $formatConf->getInt('minSizeX'),
                $formatConf->getInt('minSizeY'),
                $formatConf->getInt('webpQuality'),
            ))
            ->toArray()
        ;

        $this->httpConf = new HttpConf(
            (new RouteDefParser())->parse($confParams->getAppObject('rootRoute')),
            $this->handleExceptions,
            $confParams['csp'],
            new ErrorControllerConf(
                str_replace('.', '\\', $confParams['errorControllers']['alreadyLoggedInFqcn']),
                str_replace('.', '\\', $confParams['errorControllers']['defaultErrorFqcn']),
                str_replace('.', '\\', $confParams['errorControllers']['methodNotSupportedFqcn']),
                str_replace('.', '\\', $confParams['errorControllers']['notFoundFqcn']),
                str_replace('.', '\\', $confParams['errorControllers']['notLoggedInFqcn']),
            ),
        );

        $this->confData = CollectionFactory::createDeepAppObject($confParams);
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
        return "$this->appRootPath/$this->uploadRelPath";
    }

    /**
     * @todo Add test.
     */
    public function getSetting(string $keyPath): string
    {
        $keys = explode('.', $keyPath);
        $data = $this->confData;
        foreach ($keys as $key) {
            $data = $data[$key];
        }
        return $data;
    }

    public function hasSetting(string $key): bool
    {
        return $this->confData->hasProperty($key);
    }
}
