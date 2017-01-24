<?php

namespace WayOfDoing\PhpUrlParsing\Test\Uri;

use PHPUnit\Framework\TestCase;
use WayOfDoing\PhpUrlParsing\Uri\QueryComponent;

class QueryComponentTest extends TestCase
{
    /**
     * Tests that a default-constructed instance is empty of values.
     */
    public function testConstructsEmpty()
    {
        $this->assertEmpty(new QueryComponent());
    }

    /**
     * Tests that isset() works for query component values.
     *
     * @param QueryComponent $query
     * @param string $key
     * @param bool $expectedIssetResult
     *
     * @dataProvider issetWorksDataProvider
     */
    public function testIssetWorks(QueryComponent $query, string $key, bool $expectedIssetResult)
    {
        $this->assertSame($expectedIssetResult, isset($query[$key]));
    }

    /**
     * Tests that the constructor with argument correctly sets object state.
     *
     * @param array $values
     *
     * @dataProvider queryValuesDataProvider
     */
    public function testConstructsWithData(array $values)
    {
        $q = new QueryComponent($values);

        foreach ($values as $key => $value) {
            $this->assertTrue(isset($q[$key]));
            $this->assertSame($q[$key], $value);
        }
    }

    /**
     * Tests that unset() works for query component values.
     *
     * @param array $values
     *
     * @dataProvider queryValuesDataProvider
     */
    public function testUnsetWorks(array $values)
    {
        $q = new QueryComponent($values);

        foreach ($values as $key => $value) {
            unset($q[$key]);
            $this->assertFalse(isset($q[$key]));
        }
    }

    /**
     * Tests that array-style access for writing works.
     *
     * @param array $values
     *
     * @dataProvider queryValuesDataProvider
     */
    public function testSetWorks(array $values)
    {
        $q = new QueryComponent();

        foreach ($values as $key => $value) {
            $q[$key] = $value;
            $this->assertSame($q[$key], $value);
        }
    }

    /**
     * Tests that count() works.
     *
     * @param array $values
     *
     * @dataProvider queryValuesDataProvider
     */
    public function testCountWorks(array $values)
    {
        $this->assertCount(count($values), new QueryComponent($values));
    }

    /**
     * Tests that conversion to string through toString() works.
     *
     * @param QueryComponent $query
     * @param $expectedStringConversion
     *
     * @dataProvider toStringDataProvider
     */
    public function testExplicitToStringWorks(QueryComponent $query, $expectedStringConversion)
    {
        $this->assertSame($expectedStringConversion, $query->toString());
    }

    public function issetWorksDataProvider()
    {
        $emptyQuery = new QueryComponent();

        yield 'empty query' => [$emptyQuery, 'foo', false];

        $queryWithNonNullValue = new QueryComponent();
        $queryWithNonNullValue['foo'] = true;

        yield 'query with non-null value' => [$queryWithNonNullValue, 'foo', true];

        $queryWithNullValue = new QueryComponent();
        $queryWithNullValue['foo'] = null;

        yield 'query with null value' => [$queryWithNullValue, 'foo', true];
    }

    public function queryValuesDataProvider()
    {
        yield 'empty' => [[]];

        yield 'single value' => [['foo' => 'bar']];

        yield 'many values' => [range(0, 1000)];

        yield 'nulls' => [array_fill_keys(['foo', 'bar'], null)];

        $binaryString = implode('', array_map('chr', range(0, 255)));
        yield 'binary key and value' => [[$binaryString => $binaryString]];
    }

    public function toStringDataProvider()
    {
        yield 'empty' => [new QueryComponent(), ''];

        yield 'single value' => [new QueryComponent(['foo' => 'bar']), 'foo=bar'];

        yield 'two values' => [new QueryComponent(['foo' => 'bar', 'abc' => 'xyz']), 'foo=bar&abc=xyz'];

        yield 'null values' => [new QueryComponent(array_fill_keys(['a', 'b', 'c'], null)), 'a=&b=&c='];

        yield 'empty values' => [new QueryComponent(array_fill_keys(['a', 'b', 'c'], '')), 'a=&b=&c='];

        yield 'false values' => [new QueryComponent(array_fill_keys(['a', 'b', 'c'], false)), 'a=0&b=0&c=0'];

        yield 'true values' => [new QueryComponent(array_fill_keys(['a', 'b', 'c'], true)), 'a=1&b=1&c=1'];

        yield 'numeric keys' => [new QueryComponent(array_fill_keys([1, '2', 3], true)), '1=1&2=1&3=1'];

        yield 'nested array' => [new QueryComponent(['x' => ['y' => ['z' => 'foo']]]), rawurlencode('x[y][z]').'=foo'];

        $binaryString = implode('', array_map('chr', range(0, 255)));

        yield 'binary key' => [new QueryComponent([$binaryString => 'foo']), rawurlencode($binaryString).'=foo'];

        yield 'binary value' => [new QueryComponent(['foo' => $binaryString]), 'foo='.rawurlencode($binaryString)];
    }
}
