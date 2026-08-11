<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

class BoolModel extends AbstractScalar
{
    #[\Override]
    public function isBool(): bool {
        return true;
    }
}