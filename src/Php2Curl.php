<?php

namespace ThinLayer\Php2Curl;

class Php2Curl
{
    public static function build(): string
    {
        return (new CurlBuilder())
            ->headers(getallheaders() ?? [])
            ->input(file_get_contents('php://input'))
            ->method($_SERVER['REQUEST_METHOD'])
            ->scheme($_SERVER['REQUEST_SCHEME'])
            ->host($_SERVER['SERVER_NAME'])
            ->port($_SERVER['SERVER_PORT'])
            ->uri($_SERVER['REQUEST_URI'])
            ->post($_POST)
            ->build();
    }
}
