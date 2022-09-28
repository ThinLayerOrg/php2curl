<?php

namespace ThinLayer\Php2Curl;

class Php2Curl
{
    public static function build(?string $host = null, ?int $port = null, ?string $scheme = null, ?string $method = null): string
    {
        return (new CurlBuilder())
            ->headers(getallheaders() ?? [])
            ->input(file_get_contents('php://input'))
            ->method($method ?? $_SERVER['REQUEST_METHOD'])
            ->scheme($scheme ?? $_SERVER['REQUEST_SCHEME'])
            ->host($host ?? $_SERVER['SERVER_NAME'])
            ->port($port ?? $_SERVER['SERVER_PORT'])
            ->uri($_SERVER['REQUEST_URI'])
            ->post($_POST)
            ->build();
    }
}
