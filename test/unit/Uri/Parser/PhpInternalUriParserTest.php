<?php

namespace WayOfDoing\PhpUrlParsing\Test\Uri\Parser;

use WayOfDoing\PhpUrlParsing\Uri\Parser\PhpInternalUriParser;
use WayOfDoing\PhpUrlParsing\Uri\Parser\UriParserInterface;

class PhpInternalUriParserTest extends ParserTestBase
{
    protected function createParser(): UriParserInterface
    {
        return new PhpInternalUriParser();
    }
}
