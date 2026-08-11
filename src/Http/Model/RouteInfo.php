<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Model;

final class RouteInfo {
    // private(set) string $controllerFqcn;

    /**
     * @todo Instead of allowsAdmins and allowsVisitors, we could have allowedRoles, which would be a set of strings.
     */
    public function __construct(
        private(set) string $controllerFqcn,
        private(set) int $nArgs,
        private(set) bool $allowsAdmins,
        private(set) bool $allowsVisitors,
    )
    {
    }
}