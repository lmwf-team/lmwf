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
     * @todo Create class for the returned object.
     * @return array Return the controller FQCN and the number of
     * parameters it takes.
     */
    public function getControllerFqcn(array $pathSegments): array
    {
        $currentRoute = $this->configData->getAppObject('rootRoute');
        $nPathSegments = count($pathSegments);
        $i = 0;
        $roles = [
            'admins' => true,
            'visitors' => true,
        ];
        if ($currentRoute->hasProperty('roles')) {
            foreach ($currentRoute['roles'] as $roleId => $isAuthorized) {
                if (false === $isAuthorized && key_exists($roleId, $roles)) {
                    $roles[$roleId] = false;
                }
            }
        }
        while ($i < $nPathSegments) {
            if ($currentRoute->hasProperty('routes') && $currentRoute->getAppObject('routes')->hasProperty($pathSegments[$i])) {
                $currentRoute = $currentRoute['routes'][$pathSegments[$i]];
                if ($currentRoute->hasProperty('roles')) {
                    foreach ($currentRoute['roles'] as $roleId => $isAuthorized) {
                        if (false === $isAuthorized && key_exists($roleId, $roles)) {
                            $roles[$roleId] = false;
                        }
                    }
                }
            } elseif ($currentRoute->hasProperty('controller')) {
                $nRemainingPathSegments = $nPathSegments - $i;
                $maxNArgs = $currentRoute['controller']['max_n_args'] ?? $currentRoute['controller']['n_args'] ?? 0;
                $minNArgs = $currentRoute['controller']['min_n_args'] ?? $currentRoute['controller']['n_args'] ?? 0;
                if ($nRemainingPathSegments <= $maxNArgs && $nRemainingPathSegments >= $minNArgs) {
                    break;
                } else {
                    throw new SettingNotFoundException("Found a route but not the right number of arguments ($nRemainingPathSegments not between $minNArgs and $maxNArgs.");
                }
            } else {
                throw new SettingNotFoundException("Requested route with path segment {$pathSegments[$i]} does not exist.");
            }
            $i++;
        }
        if ($currentRoute->hasProperty('roles')) {
            foreach ($currentRoute['roles'] as $roleId => $isAuthorized) {
                if (false === $isAuthorized && key_exists($roleId, $roles)) {
                    $roles[$roleId] = false;
                }
            }
        }

        if (!$currentRoute->hasProperty('controller')) {
            throw new SettingNotFoundException("Requested route does not have an associated controller.");
        }

        $controllerRoute = $currentRoute['controller']->toArray();
        $controllerRoute['n_args'] = $nPathSegments - $i;
        $controllerRoute['roles'] = $roles;
        return $controllerRoute;
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
        return $this->configData['rootRoute'];
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
