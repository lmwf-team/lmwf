<?php

declare(strict_types=1);

namespace LMWF\Tests\Mocks;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * @template T
 */
final class ContainerMock implements ContainerInterface
{
    /**
     * @param array<class-string, T> $services
     */
    public function __construct(
        private array $services,
    ) {
    }

    /**
     * @return T
     */
    #[\Override]
    public function get(string $id)
    {
        if (key_exists($id, $this->services)) {
            return $this->services[$id];
        }
        throw new class extends RuntimeException implements NotFoundExceptionInterface {
        };
    }

    #[\Override]
    public function has(string $id): bool
    {
        return key_exists($id, $this->services);
    }
}
