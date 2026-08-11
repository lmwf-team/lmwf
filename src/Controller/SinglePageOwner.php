<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use LM\WebFramework\DataStructures\Page;

interface SinglePageOwner
{
    public function getPage(): Page;
}
