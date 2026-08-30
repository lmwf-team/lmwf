<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

use LMWF\Http\Controller\IRoutedController;

/**
 * @todo Rename
 */
interface IPageConf
{
    public function getTitle(): string;

    /**
     * @return class-string<IRoutedController>
     */
    public function getControllerFqcn(): string;

    /**
     * The app's base URL, without trailing slash.
     */
    public function getBaseUrl(): string;
    
    public function isIndexed(): bool;

    public function isInHierarchy(): bool;
}
