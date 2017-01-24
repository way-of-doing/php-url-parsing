<?php

namespace WayOfDoing\PhpUrlParsing\Uri\Parser;

use WayOfDoing\PhpUrlParsing\Uri\UriInterface;

/**
 * Represents an object that can parse a string into a {@link UriInterface}.
 *
 * Note that the mechanism of parsing and the level of compliance with RFC 3986 is deliberately unspecified.
 */
interface UriParserInterface
{
    /**
     * @param string $uri The URI to parse.
     *
     * @return UriInterface
     */
    public function parseUri(string $uri): UriInterface;
}
