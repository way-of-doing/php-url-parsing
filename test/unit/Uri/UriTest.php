<?php

namespace WayOfDoing\PhpUrlParsing\Test\Uri;

use PHPUnit\Framework\TestCase;
use WayOfDoing\PhpUrlParsing\Uri\Uri;

class UriTest extends TestCase
{
    /**
     * Tests that recomposition to string format works as per the spec.
     *
     * @param Uri $uri
     * @param string $expectedConversion
     *
     * @dataProvider toStringDataProvider
     */
    public function testToString(Uri $uri, string $expectedConversion)
    {
        $this->assertSame($expectedConversion, $uri->toString());
    }

    public function toStringDataProvider()
    {
        yield 'empty' => [new Uri(), ''];

        $uri = new Uri();
        $uri->setPath('/foo');

        yield 'path only' => [$uri, '/foo'];

        $uri = new Uri();
        $uri->setScheme('mailto');
        $uri->setPath('foo');

        yield 'scheme + path' => [$uri, 'mailto:foo'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setHost('1.2.3.4');
        $uri->setPath('/foo');

        yield 'scheme + host + path' => [$uri, 'http://1.2.3.4/foo'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setHost('1.2.3.4');
        $uri->setPort('1234');
        $uri->setPath('/foo');

        yield 'scheme + host + port + path' => [$uri, 'http://1.2.3.4:1234/foo'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setUserName('user');
        $uri->setHost('1.2.3.4');
        $uri->setPath('/foo/');

        yield 'scheme + user + host + path' => [$uri, 'http://user@1.2.3.4/foo/'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setUserName('user');
        $uri->setPassword('');
        $uri->setHost('1.2.3.4');
        $uri->setPath('/foo/');

        yield 'scheme + user + password + host + path' => [$uri, 'http://user:@1.2.3.4/foo/'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setUserName('~user!~');
        $uri->setPassword('!@#x');
        $uri->setHost('www.example.com');
        $uri->setPort('8080');
        $uri->setPath('/foo/');

        yield 'scheme + user + password + host + port + path' => [$uri, 'http://~user%21~:%21%40%23x@www.example.com:8080/foo/'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setHost('www.example.com');
        $uri->setQueryComponent(['foo' => 'bar']);

        yield 'scheme + host + query' => [$uri, 'http://www.example.com?foo=bar'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setHost('www.example.com');
        $uri->setPath('/');
        $uri->setQueryComponent(['foo' => ['bar' => 'has space'], '0' => false]);

        yield 'scheme + host + path + query' => [$uri, 'http://www.example.com/?foo%5Bbar%5D=has%20space&0=0'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setHost('www.example.com');
        $uri->setFragment('');

        yield 'scheme + host + fragment' => [$uri, 'http://www.example.com#'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setHost('www.example.com');
        $uri->setPath('/');
        $uri->setQueryComponent(['foo' => ['bar' => 'has space'], '0' => null]);
        $uri->setFragment('~()');

        yield 'scheme + host + path + query + fragment' => [$uri, 'http://www.example.com/?foo%5Bbar%5D=has%20space&0=#~%28%29'];

        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setHost('www.example.com');
        $uri->setFragment('motörhead');

        yield 'scheme + host + fragment' => [$uri, 'http://www.example.com#mot%C3%B6rhead'];
    }
}
