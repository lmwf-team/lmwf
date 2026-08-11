<?php

namespace LM\WebFramework\Http\Model;

use LM\WebFramework\DataStructures\AppObject;
use UnexpectedValueException;

final class RouteInfoFactory
{
    const string SUBROUTES_KEY = 'routes';
    const string ALLOW_ADMINS_KEY = 'allowsAdmins';
    const string ALLOW_VISITORS_KEY = 'allowsVisitors';
    const string ARGS_KEY = 'args';
    const string FQCN_KEY = 'fqcn';

    private(set) readonly bool $allowAdmins;
    private(set) readonly bool $allowVisitors;
    private(set) readonly ?string $fqcn;
    private(set) readonly array $routes;

    public function __construct(AppObject $conf, bool $allowAdmins = true, bool $allowVisitors = true)
    {
        if ($conf->hasProperty(self::SUBROUTES_KEY) && $conf->hasProperty(self::ARGS_KEY)) {
            throw new UnexpectedValueException("Routes are not allowed to have both a \"{self::SUBROUTES_KEY}\" and an \"{self::ARGS_KEY}\" property.");
        } elseif (
            !$conf->hasProperty(self::SUBROUTES_KEY)
            && !$conf->hasProperty(self::ARGS_KEY)
            && !$conf->hasProperty(self::FQCN_KEY)
        ) {
            throw new UnexpectedValueException("Route neither defines a \"{self::SUBROUTES_KEY}\", an \"{self::ARGS_KEY}\" nor an \"{self::FQCN_KEY}\" property.");
        }

        $this->allowAdmins = $conf[self::ALLOW_ADMINS_KEY] ?? $allowAdmins;
        $this->allowVisitors = $conf[self::ALLOW_VISITORS_KEY] ?? $allowVisitors;

        $routes = [];
        if ($conf->hasProperty(self::SUBROUTES_KEY)) {
            foreach ($conf->getAppObject(self::SUBROUTES_KEY) as $segment => $routeConf) {
                $routes[$segment] = new self($routeConf, $this->allowAdmins, $this->allowVisitors);
            }
        } elseif ($conf->hasProperty(self::ARGS_KEY)) {
            foreach ($conf[self::ARGS_KEY] as $nArgs => $nArgsConf) {
                $routes[$nArgs] = new RouteInfo(
                    $nArgsConf[self::FQCN_KEY] ?? $conf[self::FQCN_KEY],
                    $nArgs,
                    $nArgsConf[self::ALLOW_ADMINS_KEY] ?? $this->allowAdmins,
                    $nArgsConf[self::ALLOW_VISITORS_KEY] ?? $this->allowVisitors,
                );
            }
        } else {
            $routes[] = new RouteInfo(
                $conf[self::FQCN_KEY],
                0,
                $this->allowAdmins,
                $this->allowVisitors,
            );
        }
        $this->routes = $routes;
    }

    public function hasSubroutes(): bool
    {
        return !array_is_list($this->routes);
    }
}
