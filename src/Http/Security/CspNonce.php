<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Security;

use Stringable;

/**
 * @todo Should be part of the server request or something similar.
 */
final readonly class CspNonce implements Stringable
{
    public string $nonce;

    public function __construct()
    {
        $this->nonce = base64_encode(random_bytes(16));
    }

    public function __toString(): string
    {
        return $this->nonce;
    }
}