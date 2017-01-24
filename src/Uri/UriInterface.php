<?php

namespace WayOfDoing\PhpUrlParsing\Uri;

/**
 * Represents a read-only value object that stands in for a URI.
 */
interface UriInterface
{
    /**
     * Get the scheme part of the URI, if specified.
     *
     * @return string|null
     */
    public function getScheme();

    /**
     * Get the host part of the URI, if specified.
     *
     * @return string|null
     */
    public function getHost();

    /**
     * Get the port part of the URI, if specified.
     *
     * @return int|null
     */
    public function getPort();

    /**
     * Get the user part of the URI, if specified.
     *
     * @return string|null
     */
    public function getUserName();

    /**
     * Get the password part of the URI, if specified.
     *
     * @return string|null
     */
    public function getPassword();

    /**
     * Get the path part of the URI, if specified.
     *
     * @return string|null
     */
    public function getPath();

    /**
     * Get the query component of the URI.
     *
     * @return QueryComponent
     */
    public function getQueryComponent(): QueryComponent;

    /**
     * Get the fragment part of the URI, if specified.
     *
     * @return string|null
     */
    public function getFragment();

    /**
     * Gets the string representation of the URI by recomposing it from its components exactly
     * as described in RFC 3986 section 5.3 ("Component Recomposition").
     *
     * @return string
     */
    public function toString();
}
