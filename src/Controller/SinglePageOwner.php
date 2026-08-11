<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use LM\WebFramework\DataStructures\Page;

/**
 * @todo Delete?
 */
interface SinglePageOwner
{
    public function getPage(): Page;
}
