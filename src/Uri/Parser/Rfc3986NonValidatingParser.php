<?php

namespace WayOfDoing\PhpUrlParsing\Uri\Parser;

use WayOfDoing\PhpUrlParsing\Uri\Uri;
use WayOfDoing\PhpUrlParsing\Uri\UriInterface;

/**
 * URI parser based on the non-validating regular expression provided in Appendix B of RFC 3986
 * plus a bit of custom logic.
 *
 * This is not a validating parser; it will parse some subset of the URIs that are not valid according to RFC 3986.
 */
class Rfc3986NonValidatingParser implements UriParserInterface
{
    /**
     * @var string Copied from Appendix B of RFC 3986.
     */
    const REGEX_URI_NON_VALIDATING = '~^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$~';

    /**
     * @inheritdoc
     *
     * @throws UriParserException if the specified URI is malformed to the point of not being parsable.
     */
    public function parseUri(string $uri): UriInterface
    {
        if (!preg_match(self::REGEX_URI_NON_VALIDATING, $uri, $parsed)) {
            throw new UriParserException(sprintf('malformed URL: %s', $uri));
        }

        // This is also copied from the RFC
        $keyMap = [
            2 => 'scheme',
            4 => 'authority',
            5 => 'path',
            7 => 'query',
            9 => 'fragment',
        ];

        // The following lines perform the following transformations to $parsed:
        // 1. Replace integer keys with descriptive strings.
        // 2. Remove the keys for empty or missing values.
        $parsed = array_intersect_key($parsed, $keyMap);
        $parsed = array_combine($keyMap, $parsed + array_fill_keys(array_keys($keyMap), null));
        $parsed = array_filter($parsed, 'strlen');

        $result = new Uri();

        if (isset($parsed['scheme'])) {
            $result->setScheme($parsed['scheme']);
        }
        if (isset($parsed['authority'])) {
            list ($userName, $password, $host, $port) = $this->parseAuthority($parsed['authority']);
            $result->setUserName(rawurldecode($userName));
            $result->setPassword(rawurldecode($password));
            $result->setHost(rawurldecode($host));
            $result->setPort($port);
        }
        if (isset($parsed['path'])) {
            $result->setPath($parsed['path']);
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

    /**
     * Parses the authority part of a URI by splitting it into its components.
     *
     * @param string $authority The authority to be parsed.
     *
     * @return array A numerically indexed array that contains, in order: username, password, host, and port values.
     */
    private function parseAuthority(string $authority): array
    {
        $userName = $password = $host = $port = null;

        if (strpos($authority, '@') !== false) {
            list($userInfo, $authority) = explode('@', $authority, 2);
            if (strpos($userInfo, ':') !== false) {
                list ($userName, $password) = explode(':', $userInfo, 2);
            }
            else {
                $userName = $userInfo;
            }
        }

        $lastColonPos = strrpos($authority, ':');
        if ($lastColonPos !== false) {
            // Careful here because the IPv6 address format uses double colons too
            $port = substr($authority, $lastColonPos + 1);
            $port = ctype_digit($port) ? (int)$port : null;
        }

        $host = isset($port) ? substr($authority, 0, $lastColonPos) : $authority;

        return [$userName, $password, $host, $port];
    }
}
