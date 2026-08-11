<?php

namespace Lm\WebFramework\Request;

class DefaultPostRequest extends DefaultRequest implements IPostRequest
{
    private $post_array;

    public function __construct(array $server, array $post_array)
    {
        $this->post_array = $post_array;
        parent::__construct($server);
    }

    public function getPostArray(): array
    {
        return $this->post_array;
    }
}