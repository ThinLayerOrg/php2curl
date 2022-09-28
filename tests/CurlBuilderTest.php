<?php

namespace ThinLayer\Php2Curl\Tests;

use ThinLayer\Php2Curl\CurlBuilder;
use PHPUnit\Framework\TestCase;

class CurlBuilderTest extends TestCase
{
    public function testMethod()
    {
        $expected = 'curl --location --request GET "http://localhost/"';
        $actual = (new CurlBuilder())->method('GET')->build();
        self::assertEquals($expected, $actual);
    }
}
