<?php

namespace WayOfDoing\PhpUrlParsing\Test\Uri\Parser;

use PHPUnit\Framework\TestCase;
use WayOfDoing\PhpUrlParsing\Iterator\CartesianProductIterator;
use WayOfDoing\PhpUrlParsing\Uri\Parser\UriParserInterface;
use WayOfDoing\PhpUrlParsing\Uri\Uri;

/**
 * Abstract base class for testing URI parsers.
 */
abstract class ParserTestBase extends TestCase
{
    /**
     * Tests that an URI parses correctly. This is done by getting its string representation, parsing that into a new
     * URI instance and then checking that the property sets of both instances are the same, in effect proving that the
     * URI can round-trip through the parser.
     *
     * @param Uri $uri
     *
     * @dataProvider validUrlProvider
     */
    public function testUriParsesCorrectly(Uri $uri)
    {
        $parser = $this->createParser();
        $result = $parser->parseUri($uri->toString());

        $this->assertEquals(
            $uri,
            $result,
            sprintf('%s failed to parse the URI "%s", it recombines as "%s".', get_class($parser), $uri, $result)
        );
    }

    /**
     * Produces valid URI instances for all possible combinations of valid URI component values.
     *
     * Combinatorial explosion warning, don't add valid component values willy-nilly!
     *
     * @return \Traversable
     */
    public function validUrlProvider()
    {
        $iterator = new CartesianProductIterator(
            $this->convertToArray($this->getValidSchemeValues()),
            $this->convertToArray($this->getValidUserInfoValues()),
            $this->convertToArray($this->getValidHostValues()),
            $this->convertToArray($this->getValidPortValues()),
            $this->convertToArray($this->getValidPathValues()),
            $this->convertToArray($this->getValidQueryValues()),
            $this->convertToArray($this->getValidFragmentValues())
        );

        foreach ($iterator as list ($scheme, $userInfo, $host, $port, $path, $query, $fragment)) {
            list ($user, $pass) = array_pad(explode(':', $userInfo, 2), 2, null);

            $uri = new Uri();
            $uri->setScheme($scheme);
            $uri->setUserName($user);
            $uri->setPassword($pass);
            $uri->setHost($host);
            $uri->setPort($port);
            $uri->setPath($path);
            $uri->setQueryComponent($query);
            $uri->setFragment($fragment);

            yield [$uri];
        }
    }

    /**
     * Creates an instance of the parser that the current test case is targeting.
     *
     * @return UriParserInterface
     */
    abstract protected function createParser(): UriParserInterface;

    protected function getValidSchemeValues()
    {
        return [null, 'https'];
    }

    protected function getValidUserInfoValues()
    {
        yield 'none' => null;
        yield 'user' => 'foo';
        yield 'user + pass' => '~user!:bar';
        yield 'user + empty pass' => 'foo:';
    }

    protected function getValidHostValues()
    {
        yield 'ipv4-1s' => '1.1.1.1';
        yield 'ipv4-255s' => '255.255.255.255';
        yield 'ipv6-localhost' => '[::1]';
        yield 'google' => 'www.google.com';
    }

    protected function getValidPortValues()
    {
        yield 'none' => null;
        yield 'low' => 1;
        yield 'high' => 65535;
        yield 'http' => 80;
    }

    protected function getValidPathValues()
    {
        yield 'none' => null;
        yield 'root' => '/';
        yield 'rooted, one deep, explicit' => '/www.google.com';
        yield 'rooted, two deep, explicit' => '/www.google.com/motörhead';
        yield 'rooted, two deep, implicit' => '/www.google.com/';
    }

    protected function getValidQueryValues()
    {
        yield 'none' => [];
        yield 'alpha keys, non-null values' => ['foo' => 1, 'bar' => 2, 'motörhead' => true];
        yield 'alpha keys, null and empty values' => ['foo' => null, 'bar' => 0, 'baz' => ''];
        yield 'digit keys, non-null values' => range(0, 1);
        yield 'nested array' => ['foo' => ['bar' => range(0, 2)]];
    }

    protected function getValidFragmentValues()
    {
        return [null, 'motörhead'];
    }

    private function convertToArray($traversable)
    {
        return is_array($traversable) ? $traversable : iterator_to_array($traversable);
    }
}
