<?php

namespace WayOfDoing\PhpUrlParsing\Uri\Parser;

/**
 * THIS IS UNFINISHED WORK IN PROGRESS. IT'S NOT USED, AND NOT INTENDED TO BE USED, ANYWHERE.
 */
class Rfc3986ValidatingParser
{
    /**
     * Copied from RFC
     */
    const REGEX_URI_NON_VALIDATING = '~^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$~';

    /**
     * scheme      = ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
     *
     * @var string
     */
    const REGEX_FRAGMENT_SCHEME = '(?<scheme>[a-z][a-z0-9.+-]*)';

    const REGEX_FRAGMENT_REGISTERED_NAME = '(?<registeredName>(?:[a-z0-9!$&\'()*+,;=._~-]|%[a-f0-9]{2})*)';

    const REGEX_FRAGMENT_IPV4 = '(?<ipV4>(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]))';

    const REGEX_FRAGMENT_HOST = '(?<host>'.self::REGEX_FRAGMENT_IPV4.'|'.self::REGEX_FRAGMENT_REGISTERED_NAME.')';

    /**
     * Note that this requires a non-captured @ in the input to allow a match!
     */
    const REGEX_FRAGMENT_USER_INFO = '(?:(?<userInfo>(?=.+@)(?:[a-z0-9!$&\'()*+,;=:._~-]|%[a-f0-9]{2})*)@)?';

    const REGEX_FRAGMENT_PORT = '(?::(?<port>6553[0-5]|655[0-2]\d|65[0-4]\d{2}|6[0-4]\d{3}|0*\d{1,4}))?'; // TODO: not actually compliant, RFC doesn't range restrict


    /**
     * query       = *( pchar / "/" / "?" )
     * pchar       = unreserved / pct-encoded / sub-delims / ":" / "@"
     * unreserved  = ALPHA / DIGIT / "-" / "." / "_" / "~"
     * sub-delims    = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
     */
    const REGEX_FRAGMENT_QUERY = '(?:\?(?<query>(?:[a-z0-9\/?:@!$&\'()*+,;=._~-]|%[a-f0-9]{2})*))?';

    /**
     * fragment    = *( pchar / "/" / "?" )
     * pchar       = unreserved / pct-encoded / sub-delims / ":" / "@"
     * unreserved  = ALPHA / DIGIT / "-" / "." / "_" / "~"
     * sub-delims  = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
     */
    const REGEX_FRAGMENT_FRAGMENT = '(?:#(?<fragment>(?:[a-z0-9\/?:@!$&\'()*+,;=._~-]|%[a-f0-9]{2})*))?';

    const REGEX_FRAGMENT_AUTHORITY = '(?<authority>'.self::REGEX_FRAGMENT_USER_INFO.self::REGEX_FRAGMENT_HOST.self::REGEX_FRAGMENT_PORT.')';

    const REGEX_FRAGMENT_PATH = ''; // TODO

    const REGEX_FRAGMENT_HIERARCHICAL = '(?:\/\/'.self::REGEX_FRAGMENT_AUTHORITY.')'.self::REGEX_FRAGMENT_PATH;

    public function parseUri($uri)
    {
        if (!preg_match(self::REGEX_URI_NON_VALIDATING, $uri, $matches)) {
            throw new \RuntimeException("Severely malformed URI");
        }

        $keyMap = [
            2 => 'scheme',
            4 => 'authority',
            5 => 'path',
            7 => 'query',
            9 => 'fragment',
        ];

        $matches = array_intersect_key($matches, $keyMap);
        $matches = array_combine($keyMap, $matches + array_fill_keys(array_keys($keyMap), null));
        $matches = array_filter($matches, 'strlen');

        if (isset($matches['scheme']) && !$this->isValidScheme($matches['scheme'])) {
            throw new \RuntimeException("Invalid scheme $matches[scheme]");
        }

        if (isset($matches['authority'])) {
            $au = $this->parseAuthority($matches['authority']);
        }

        if (isset($matches['path'])) {

        }

        if (isset($matches['query']) && !$this->isValidQuery($matches['query'])) {
            throw new \RuntimeException("Invalid query $matches[query]");
        }

        if (isset($matches['fragment']) && !$this->isValidFragment($matches['fragment'])) {
            throw new \RuntimeException("Invalid fragment");
        }

        print_r($matches);

        return true;
    }

    public function parseUrl($url)
    {
        $fragment = self::REGEX_FRAGMENT_SCHEME.':'.self::REGEX_FRAGMENT_HIERARCHICAL.self::REGEX_FRAGMENT_QUERY.self::REGEX_FRAGMENT_FRAGMENT;

        if (!preg_match($this->regexFromFragment($fragment), $url, $matches)) {
            return false;
        }

        var_dump($matches);

        return true;
    }

    public function parseAuthority(string $authority)
    {
        $returnedKeys = array_flip(['userInfo', 'host', 'ipV4', 'registeredName', 'port']);
        // unreserved  = ALPHA / DIGIT / "-" / "." / "_" / "~"
        // userinfo    = *( unreserved / pct-encoded / sub-delims / ":" )
        //         sub-delims  = "!" / "$" / "&" / "'" / "(" / ")" "*" / "+" / "," / ";" / "="
        // host        = IP-literal / IPv4address / reg-name
        // IP-literal = "[" ( IPv6address / IPvFuture  ) "]"
        // IPvFuture  = "v" 1*HEXDIG "." 1*( unreserved / sub-delims / ":" )

        $fragment = self::REGEX_FRAGMENT_USER_INFO.self::REGEX_FRAGMENT_HOST.self::REGEX_FRAGMENT_PORT;
        if (!preg_match($this->regexFromFragment($fragment), $authority, $matches)) {
            return false;
        }

        return array_intersect_key($matches, $returnedKeys);
    }

    public function isValidScheme($scheme)
    {
        return preg_match($this->regexFromFragment(self::REGEX_FRAGMENT_SCHEME), $scheme);
    }

    public function isValidAuthority($authority)
    {

    }

    public function isValidIpV4($ipV4)
    {
        return preg_match($this->regexFromFragment(self::REGEX_FRAGMENT_IPV4), $ipV4);
    }

    public function isValidRegisteredName($registeredName)
    {
        return preg_match($this->regexFromFragment(self::REGEX_FRAGMENT_REGISTERED_NAME), $registeredName);
    }

    public function isValidHost($host)
    {
        // TODO: this does not cover IP-literal from the RFC
//      IP-literal = "[" ( IPv6address / IPvFuture  ) "]"
//      IPvFuture  = "v" 1*HEXDIG "." 1*( unreserved / sub-delims / ":" )
//        IPv6address =                            6( h16 ":" ) ls32
//    /                       "::" 5( h16 ":" ) ls32
//    / [               h16 ] "::" 4( h16 ":" ) ls32
//    / [ *1( h16 ":" ) h16 ] "::" 3( h16 ":" ) ls32
//    / [ *2( h16 ":" ) h16 ] "::" 2( h16 ":" ) ls32
//    / [ *3( h16 ":" ) h16 ] "::"    h16 ":"   ls32
//    / [ *4( h16 ":" ) h16 ] "::"              ls32
//    / [ *5( h16 ":" ) h16 ] "::"              h16
//    / [ *6( h16 ":" ) h16 ] "::"
//
//      ls32        = ( h16 ":" h16 ) / IPv4address
//    ; least-significant 32 bits of address
//
//      h16         = 1*4HEXDIG
//    ; 16 bits of address represented in hexadecimal

        return preg_match($this->regexFromFragment(self::REGEX_FRAGMENT_HOST), $host);
    }

    public function isValidPort($port)
    {
        return preg_match($this->regexFromFragment(self::REGEX_FRAGMENT_PORT), ':'.$port);
    }

    public function isValidQuery($queryString)
    {
        return preg_match($this->regexFromFragment(self::REGEX_FRAGMENT_QUERY), '?'.$queryString);
    }

    public function isValidFragment($fragment)
    {
        return preg_match($this->regexFromFragment(self::REGEX_FRAGMENT_FRAGMENT), '#'.$fragment);
    }

    public function isValidPath($path)
    {
        return true; // TODO
//        URI         = scheme ":" hier-part [ "?" query ] [ "#" fragment ]
//        scheme      = ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
//        hier-part   = "//" authority path-abempty
//    / path-absolute
//    / path-rootless
//    / path-empty

//        path          = path-abempty    ; begins with "/" or is empty
//                    / path-absolute   ; begins with "/" but not "//"
//    / path-noscheme   ; begins with a non-colon segment
//    / path-rootless   ; begins with a segment
//    / path-empty      ; zero characters
//
//      path-abempty  = *( "/" segment )
//      path-absolute = "/" [ segment-nz *( "/" segment ) ]
//      path-noscheme = segment-nz-nc *( "/" segment )
//      path-rootless = segment-nz *( "/" segment )
//      path-empty    = 0<pchar>
//      segment       = *pchar
//      segment-nz    = 1*pchar
//      segment-nz-nc = 1*( unreserved / pct-encoded / sub-delims / "@" )
//      ; non-zero-length segment without any colon ":"
//
//      pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"

        $x = '(?<fragment>(?:[a-z0-9:@!$&\'()*+,;=._~-]|%[a-f0-9]{2})*)';
    }

    private function regexFromFragment($regex)
    {
        return '/^(?:'.$regex.')$/i';
    }
}
