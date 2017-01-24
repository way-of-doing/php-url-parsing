<?php

namespace WayOfDoing\PhpUrlParsing\Test\Iterator;

use PHPUnit\Framework\TestCase;
use Traversable;
use WayOfDoing\PhpUrlParsing\Iterator\CartesianProductIterator;

class CartesianProductIteratorTest extends TestCase
{
    /**
     * Tests that an empty input set produces an empty cartesian product.
     */
    public function testEmptyInputSetProducesEmptyResultSet()
    {
        $this->assertCount(0, new CartesianProductIterator());
    }

    /**
     * Tests that sets of empty input traversables produce an empty cartesian product.
     *
     * @param array|Traversable[] $inputs These get fed into the constructor.
     *
     * @dataProvider setOfEmptyInputsProvider
     */
    public function testSetOfEmptyInputsProducesEmptyResultSet($inputs)
    {
        // Sadly PHPUnit doesn't play well with generators when asserting count :(
        // so we have to materialize the sequence explicitly.
        $actualResult = iterator_to_array(new CartesianProductIterator(...$inputs));
        $this->assertEmpty($actualResult);
    }

    /**
     * Tests that cartesian products are calculated correctly.
     *
     * @param array|Traversable[] $inputs These get fed into the constructor.
     * @param array $expectedResult The expected cartesian product.
     *
     * @dataProvider nonEmptyCartesianProductResultSetProvider
     */
    public function testCartesianProductResultSet($inputs, $expectedResult)
    {
        $actualResult = iterator_to_array(new CartesianProductIterator(...$inputs));
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Tests that the presence of one or more empty inputs results in an empty cartesian product.
     *
     * @param array|Traversable[] $inputs These get fed into the constructor.
     *
     * @dataProvider emptyCartesianProductResultSetProvider
     */
    public function testAnyEmptyInputAnywhereProducesEmptyResultSet($inputs)
    {
        $actualResult = iterator_to_array(new CartesianProductIterator(...$inputs));
        $this->assertCount(0, $actualResult);
    }

    /**
     * Tests that memory usage does not grow when a large cartesian product is iterated over; this proves that the
     * product is being incrementally calculated instead of materialized up front.
     */
    public function testResultSetIsNotMaterializedInternally()
    {
        $infiniteIterator = new \InfiniteIterator(new \ArrayIterator(['foo']));
        $cartesianProductIterator = new CartesianProductIterator($infiniteIterator, $infiniteIterator);
        $innerIterator = $cartesianProductIterator->getIterator();

        $itemsSeen = 0;
        $initialMemoryUsage = memory_get_usage(true);

        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($innerIterator as $item) {
            $this->assertLessThan(1000, memory_get_usage(true) - $initialMemoryUsage);
            if (++$itemsSeen > 1e4) {
                break;
            }
        }
    }

    public function setOfEmptyInputsProvider()
    {
        yield 'empty array' => [[[]]];
        yield 'empty object' => [[new \stdClass()]];
        yield 'EmptyIterator' => [[new \EmptyIterator()]];
        yield 'empty ArrayIterator' => [[new \ArrayIterator()]];
        yield 'multiple EmptyIterator' => [[new \EmptyIterator(), new \EmptyIterator()]];
    }

    public function nonEmptyCartesianProductResultSetProvider()
    {
        yield 'single input, single value' => [
            [['foo']],
            [['foo']],
        ];

        yield 'single input, multi value' => [
            [range(0, 100)],
            array_chunk(range(0, 100), 1),
        ];

        yield 'multi input, single value' => [
            [['foo'], ['bar']],
            [['foo', 'bar']],
        ];

        yield 'multi input, multi value' => [
            [['a', 'b'], [1, 2]],
            [['a', 1], ['a', 2], ['b', 1], ['b', 2]],
        ];

        yield 'multi input, mixed value 1' => [
            [['x'], [1, 2, 3, 4]],
            [['x', 1], ['x', 2], ['x', 3], ['x', 4]],
        ];

        $uniqueObject = new \stdClass();
        yield 'multi input, mixed value 2' => [
            [['a', 'b'], [$uniqueObject], [1, 2]],
            [['a', $uniqueObject, 1], ['a', $uniqueObject, 2], ['b', $uniqueObject, 1], ['b', $uniqueObject, 2]],
        ];
    }

    /**
     * Provider of input sets that include at least one empty input.
     *
     * @return Traversable
     */
    public function emptyCartesianProductResultSetProvider()
    {
        $emptyInputs = iterator_to_array($this->setOfEmptyInputsProvider());

        foreach ($this->nonEmptyCartesianProductResultSetProvider() as list($inputs)) {
            for ($i = 0; $i <= count($inputs); ++$i) {
                $alteredInputs = $inputs;
                foreach ($emptyInputs as $emptyIteratorSet) {
                    array_splice($alteredInputs, $i, 0, $emptyIteratorSet[0]);
                    yield [$alteredInputs];
                }
            }
        }
    }
}

