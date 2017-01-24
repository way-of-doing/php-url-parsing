<?php

namespace WayOfDoing\PhpUrlParsing\Uri;

use ArrayAccess;
use Countable;

/**
 * Array-like object that represents the query component of a URI.
 */
final class QueryComponent implements ArrayAccess, Countable
{
    /**
     * @var array
     */
    private $values;

    /**
     * @param array $values The initial values for the query component.
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Checks if a query variable with the specified name exists.
     *
     * @param string $key The name of the query variable.
     *
     * @return bool True if a query variable with the specified name exists; otherwise, false.
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Gets the value of the specified query variable.
     *
     * @param string $key The name of the query variable.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->values[$key];
    }

    /**
     * Sets the value of the specified query variable.
     *
     * @param string $key The name of the query variable.
     * @param mixed $value The value to be set.
     */
    public function offsetSet($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Removes the specified query variable.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->values[$key]);
    }

    /**
     * Counts the number of query variables.
     *
     * @return int
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * Returns the current set of query variables as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }

    /**
     * Converts the query component to its string representation.
     *
     * Points of interest:
     *
     * <ul>
     * <li>Variable names and string values will be percent-encoded as per RFC3986</li>
     * <li>Boolean values will be encoded as integer one and zero for true and false respectively</li>
     * <li>Null values will be encoded the same as empty strings (this is different from {@link http_build_query}!)</li>
     * </ul>
     *
     * @return string
     */
    public function toString()
    {
        // http_build_query() does not include keys with null values at all in its output,
        // but here we want to include them (as empty strings) so this mapping has to happen.
        $mappedValues = array_combine(
            array_keys($this->values),
            array_map(function($v) { return $v ?? ''; }, $this->values)
        );

        return http_build_query($mappedValues, null, '&', PHP_QUERY_RFC3986);
    }
}
