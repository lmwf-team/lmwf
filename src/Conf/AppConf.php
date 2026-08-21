<?php

declare(strict_types=1);

namespace LMWF\Conf;

use LMWF\DataStructures\AppList;
use LMWF\DataStructures\AppObject;
use LMWF\DataStructures\Exceptions\UnexpectedPropertyType;
use LMWF\DataStructures\Factory\CollectionFactory;
use LMWF\DataStructures\ImgFormat;
use LMWF\Http\Controller\IController;
use UnexpectedValueException;

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
     *
     * @var AppObject<mixed>
     */
    public AppObject $data;

    public bool $handleExceptions;
    public bool $isDev;

    public string $baseUrl;
    public string $language;
    public string $appRootPath;
    // @var Path, relative to the appRootPath and baseUrl, to the folder where uploaded images (resources) are stored.
    // @todo Delete, only keep publicRelPath? (some overlap)
    public string $uploadRelPath;
    // @var Path, relative to the appRootPath and baseUrl, to the folder where assets and resources are stored.
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
        $this->data = $confParams;

        $this->handleExceptions = $this->data->getBool('handleExceptions');
        $this->isDev = $this->data->getBool('isDev');

        $this->baseUrl = $this->data->getString('baseUrl');
        $this->language = $this->data->getString('language');
        $this->appRootPath = $this->data->getString('appRootPath');
        $this->uploadRelPath = $this->data->getString('uploadRelPath');
        $this->publicRelPath = $this->data->getString('publicRelPath');

        $this->thumbnailFormats = $this->data
            ->getAppObjectWithItemClass('thumbnailFormats', AppObject::class)
            ->map(fn ($formatConf) => new ImgFormat(
                $formatConf->getIntStrictlyPositive('minSizeX'),
                $formatConf->getIntStrictlyPositive('minSizeY'),
                $formatConf->getIntStrictlyPositive('webpQuality') <= 100 ? $webpQuality = $formatConf->getIntStrictlyPositive('webpQuality') : throw new UnexpectedValueException(),
            ))
            ->toArray()
        ;

        $csps = $this->readCsps($this->data);

        $this->httpConf = new HttpConf(
            (new RouteDefParser($this->baseUrl))->parse($this->data->getAppObject('rootRoute')),
            $this->handleExceptions,
            $csps,
            new ErrorControllerConf(
                $this->readErrorControllerFqcn($this->data, 'alreadyLoggedInFqcn'),
                $this->readErrorControllerFqcn($this->data, 'defaultErrorFqcn'),
                $this->readErrorControllerFqcn($this->data, 'methodNotSupportedFqcn'),
                $this->readErrorControllerFqcn($this->data, 'notFoundFqcn'),
                $this->readErrorControllerFqcn($this->data, 'notLoggedInFqcn'),
            ),
        );
    }

    /**
     * @param non-decimal-int-string $key
     */
    public function getBoolSetting(string $key): bool
    {
        return $this->data->getBool($key);
    }

    /**
     * @param non-decimal-int-string $key
     */
    public function getNullableSetting(string $key): ?string
    {
        $value = $this->data->get($key);
        if (!is_string($value) && null !== $value) {
            throw new UnexpectedPropertyType($key, 'null|string', $value);
        }
        return $value;
    }

    public function getPathOfUploadedFiles(): string
    {
        return "$this->appRootPath/$this->uploadRelPath";
    }

    /**
     * @param list<non-decimal-int-string>|non-decimal-int-string $key
     */
    public function getSetting(array|string $key): string
    {
        if (is_string($key)) {
            return $this->data->getString($key);
        }

        $length = count($key);
        $data = $this->data;
        for ($i = 0; $i < $length - 1; $i++) {
            $data = $data->getAppObject($key[$i]);
        }
        return $data->getString($key[$length - 1]);
    }

    /**
     * @param non-decimal-int-string $key
     */
    public function hasSetting(string $key): bool
    {
        return $this->data->hasKey($key);
    }

    /**
     * @param AppObject<mixed> $confParams
     * @return array<string, list<string>>
     */
    private function readCsps(AppObject $confParams): array
    {
        $csps = $confParams->getAppObject('csp');
        if (!$csps->all(fn ($csp, $_) => $csp instanceof AppList && $csp->all(fn ($value, $_) => is_string($value)))) {
            throw new UnexpectedValueException('CSP configuration is not correct.');
        }
        // @todo Remove that when PHPStan fixes the issue
        // @phpstan-ignore method.nonObject, return.type
        return $csps->map(fn ($values) => $values->toArray())->toArray();
    }

    /**
     * @param AppObject<mixed> $confParams
     * @param non-decimal-int-string $error
     * @return class-string<IController>
     */
    private function readErrorControllerFqcn(AppObject $confParams, string $error): string
    {
        $fqcn = str_replace('.', '\\', $confParams->getAppObject('errorControllers')->getString($error));
        if (!class_exists($fqcn) || !is_subclass_of($fqcn, IController::class)) {
            throw new UnexpectedValueException("The error controller FQCN ('$fqcn') is either not a valid FQCN, refers to a non-exsiting class, or to a class that does not implement IController.");
        }
        return $fqcn;
    }
}
