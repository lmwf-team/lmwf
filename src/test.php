<?php

declare(strict_types=1);

function getString(): ?string
{
    if (random_int(0, 10) > 5) {
        return null;
    }
    return 'OKLM';
}


echo substr(getString(), 0, 2);