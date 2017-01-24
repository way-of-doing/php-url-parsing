<?php

namespace WayOfDoing\PhpUrlParsing\Uri\Parser;

use WayOfDoing\PhpUrlParsing\Uri\Uri;
use WayOfDoing\PhpUrlParsing\Uri\UriInterface;

/**
 * URI parser based on the built-in {@link parse_url()} function.
 *
 * This is not a validating parser; it will parse some subset of the URIs that are not valid according to RFC 3986.
 *
 * @internal Reading the PHP sources, it seems they use their own custom parse logic.
 */
class PhpInternalUriParser implements UriParserInterface
{
    /**
     * @inheritdoc
     *
     * @throws UriParserException if the specified URI is malformed to the point of not being parsable.
     */
    public function parseUri(string $uri): UriInterface
    {
        $parsed = parse_url($uri);
        if (!$parsed) {
            throw new UriParserException(sprintf('malformed URL: %s', $uri));
        }

        $result = new Uri();

        if (isset($parsed['scheme'])) {
            $result->setScheme($parsed['scheme']);
        }
        if (isset($parsed['host'])) {
            $result->setHost(rawurldecode($parsed['host']));
        }
        if (isset($parsed['port'])) {
            $result->setPort($parsed['port']);
        }
        if (isset($parsed['user'])) {
            $result->setUserName(rawurldecode($parsed['user']));
        }
        if (isset($parsed['pass'])) {
            $result->setPassword(rawurldecode($parsed['pass']));
        }
        if (isset($parsed['path'])) {
            $result->setPath(rawurldecode($parsed['path']));
        }
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryValues);
            $result->setQueryComponent($queryValues);
        }
        if (isset($parsed['fragment'])) {
            $result->setFragment(rawurldecode($parsed['fragment']));
        }

        return $result;
    }
}
