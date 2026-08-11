<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Controller\Exception\AccessDenied;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;

final class Router
{
    public function __construct(
        private Configuration $config,
    ) {
    }

    /**
     * @todo Create class for the returned object.
     * @return array Return the controller FQCN and the number of
     * parameters it takes.
     */
    public function getControllerFqcn(array $pathSegments, ?string $requireRole = null): array
    {
        $currentRoute = $this->config->getRoutes();
        $nPathSegments = count($pathSegments);
        $i = 0;
        $roles = [
            'admins' => true,
            'visitors' => true,
        ];
        $roles = $this->updateRoles($currentRoute, $roles, $requireRole);
        while ($i < $nPathSegments) {
            if ($currentRoute->hasProperty('routes') && $currentRoute->getAppObject('routes')->hasProperty($pathSegments[$i])) {
                $currentRoute = $currentRoute['routes'][$pathSegments[$i]];
                $roles = $this->updateRoles($currentRoute, $roles, $requireRole);
            } elseif ($currentRoute->hasProperty('controller')) {
                $nRemainingPathSegments = $nPathSegments - $i;
                $maxNArgs = $currentRoute['controller']['max_n_args'] ?? $currentRoute['controller']['n_args'] ?? 0;
                $minNArgs = $currentRoute['controller']['min_n_args'] ?? $currentRoute['controller']['n_args'] ?? 0;
                if ($nRemainingPathSegments <= $maxNArgs && $nRemainingPathSegments >= $minNArgs) {
                    break;
                } else {
                    throw new RequestedResourceNotFound("Found a route but not the right number of arguments ($nRemainingPathSegments not between $minNArgs and $maxNArgs.");
                }
            } else {
                throw new RequestedResourceNotFound("Requested route with path segment {$pathSegments[$i]} does not exist.");
            }
            $i++;
        }
        $roles = $this->updateRoles($currentRoute, $roles, $requireRole);

        if (!$currentRoute->hasProperty('controller')) {
            throw new RequestedResourceNotFound("Requested route does not have an associated controller.");
        }

        $controllerRoute = $currentRoute['controller']->toArray();
        $controllerRoute['n_args'] = $nPathSegments - $i;
        $controllerRoute['roles'] = $roles;
        return $controllerRoute;
    }

    private function updateRoles(AppObject $route, array $roles, ?string $requireRole): array
    {
        if ($route->hasProperty('roles')) {
            foreach ($route['roles'] as $roleId => $isAuthorized) {
                if (false === $isAuthorized && key_exists($roleId, $roles)) {
                    if (null !== $requireRole && $roleId === $requireRole) {
                        throw new AccessDenied("{$requireRole} cannot access this resource.");
                    }
                    $roles[$roleId] = false;
                }
            }
        }

        return $roles;
    }
}