<?php

namespace WayOfDoing\PhpUrlParsing\Test\Uri\Parser;

use WayOfDoing\PhpUrlParsing\Uri\Parser\Rfc3986NonValidatingParser;
use WayOfDoing\PhpUrlParsing\Uri\Parser\UriParserInterface;

class Rfc3986NonValidatingParserTest extends ParserTestBase
{
    protected function createParser(): UriParserInterface
    {
        return new Rfc3986NonValidatingParser();
    }
}
