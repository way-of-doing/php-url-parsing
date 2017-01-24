<?php

namespace WayOfDoing\PhpUrlParsing\Iterator;

use EmptyIterator;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Produces the cartesian product of a set of inputs.
 *
 * Note that if any of the inputs is empty, the cartesian product will also be empty.
 *
 * @see https://en.wikipedia.org/wiki/Cartesian_product
 */
class CartesianProductIterator implements IteratorAggregate
{
    /**
     * @var array|Traversable[] The input sources for which to produce a cartesian product.
     */
    private $inputs;

    /**
     * @var Traversable|null Lazily-created, cached instance of an empty read-only traversable.
     */
    private static $emptyTraversable;

    /**
     * @param array|Traversable[] ...$traversables Input sources to produce a cartesian product from.
     *  If iterators, they must be rewindable! That means generators are immediately ruled out as input sources.
     *
     * @throws InvalidArgumentException if a {@link Generator} is specified as an input source.
     */
    public function __construct(...$traversables)
    {
        for ($i = 0; $i < count($traversables); ++$i) {
            if ($traversables[$i] instanceof Generator) {
                throw new InvalidArgumentException(
                    sprintf(
                        '%s can only use rewindable traversables as input; argument %d is a non-rewindable generator',
                        __CLASS__,
                        $i
                    )
                );
            }
        }

        $this->inputs = $traversables;
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return $this->inputs
            ? $this->createCartesianGeneratorRecursively($this->inputs)
            : $this->getEmptyTraversable();
    }

    /**
     * Creates a generator that produces the cartesian product of the specified inputs.
     *
     * @param array|Traversable[] $inputs
     *
     * @return Generator
     */
    private function createCartesianGeneratorRecursively(array $inputs): Generator
    {
        // Zero inputs is the recursion-end condition; the value to yield back would go through array_merge in the
        // general case, so using exactly an empty array here conveniently removes the need to include any kind of
        // special condition check in the multiplexer below.
        if (!$inputs) {
            yield [];
            return;
        }

        // Split the inputs in "head" (first one) and "tail" (all of the others); merge each item in head with each
        // item in the cartesian product of tail (recursively calculated) to get the cartesian product of all inputs.
        $head = array_shift($inputs); // and $inputs becomes the tail

        $multiplexer = function() use ($head, $inputs) {
            foreach ($head as $headValue) {
                $tailCartesianProduct = $this->createCartesianGeneratorRecursively($inputs);
                foreach ($tailCartesianProduct as $tailValues) {
                    yield array_merge([$headValue], $tailValues);
                }
            }
        };

        yield from $multiplexer();
    }

    /**
     * Returns a read-only {@link Traversable} with zero elements.
     *
     * @return Traversable
     */
    private function getEmptyTraversable(): Traversable
    {
        // Time for mini-PHP-rant. Ideally I'd want to return a straight EmptyIterator here, but apparently someone
        // thought it would be a good idea if EmptyIterator::key() threw every time it was called instead of, say,
        // returning NULL the way all other iterators do if key() is called when they are not valid().
        //
        // This, together with PHPUnit's kind of heavy-handed insistence to rewind iterators in its implementation of
        // the Count constraint, means that the combo is fatal. In the end this prohibits the most natural way to write
        // the test case "cartesian iterator of an empty input set produces an empty result set".
        //
        // Possible solutions?
        // 1. Return a LimitIterator of anything with zero limit. A bit ugly but... haha, just kidding, LimitIterator
        //    doesn't allow that. Probably the same person as above thought that would also be a good idea.
        // 2. Return an empty ArrayIterator. Kinda reasonable, but that "iterator" has a crapton of methods designed to
        //    _modify_ the backing array. And we are caching it, so someone could poison the cache for everyone. Ugh.
        // 3. Uh... write my own iterator that's actually useful here? So...
        return self::$emptyTraversable
            ?? self::$emptyTraversable = new class extends EmptyIterator
            {
                public function key()
                {
                    return null;
                }
            };
    }
}
