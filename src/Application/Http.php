<?php
namespace Tenjuu99\Reversi\Application;

use Attribute;

#[Attribute]
class Http
{
    public readonly string $method;
    public readonly string $contentType;

    public function __construct(string $method = 'Get', string $contentType = 'application/json')
    {
        $this->method = strtolower($method);
        $this->contentType = $contentType;
    }
}
