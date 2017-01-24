<?php

namespace WayOfDoing\PhpUrlParsing\Uri;

use InvalidArgumentException;

/**
 * Value object that represents a URI.
 */
final class Uri implements UriInterface
{
    /**
     * @var string|null
     */
    private $scheme;

    /**
     * @var string|null
     */
    private $host;

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var string|null
     */
    private $userName;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var QueryComponent
     */
    private $queryComponent;

    /**
     * @var string|null
     */
    private $fragment;

    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        $this->queryComponent = new QueryComponent();
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @inheritdoc
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Sets the scheme part of the URI.
     *
     * @param string|null $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * @inheritdoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the host part of the URI.
     *
     * @param string|null $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @inheritdoc
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the port part of the URI.
     *
     * @param int|null $port
     */
    public function setPort($port)
    {
        $this->port = $port === null ? null : (int)$port;
    }

    /**
     * @inheritdoc
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Sets the user part of the URI.
     *
     * @param string|null $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the password part of the URI.
     *
     * @param string|null $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @inheritdoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the path part of the URI.
     *
     * @param string|null $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @inheritdoc
     */
    public function getQueryComponent(): QueryComponent
    {
        return $this->queryComponent;
    }

    /**
     * Sets the query component of the URI.
     *
     * @param QueryComponent|array $queryComponent
     *
     * @throws InvalidArgumentException if the query component is not an array or instance of {@link QueryComponent}.
     */
    public function setQueryComponent($queryComponent)
    {
        if ($queryComponent instanceof QueryComponent) {
            $this->queryComponent = $queryComponent;
        }
        else if (is_array($queryComponent)) {
            $this->queryComponent = new QueryComponent($queryComponent);
        }
        else {
            throw new InvalidArgumentException("Query component must be an object or array");
        }
    }

    /**
     * @inheritdoc
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Sets the fragment part of the URI.
     *
     * @param string|null $fragment
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        $result = '';

        if ($this->scheme !== null) {
            $result .= $this->scheme . ':';
        }

        $hasAuthority = isset($this->userName) || isset($this->password) || isset($this->host) || isset($this->port);

        if ($hasAuthority) {
            $result .= '//';
        }

        if ($this->userName !== null) {
            if ($this->password !== null) {
                $result .= rawurlencode($this->userName) . ':' . rawurlencode($this->password) . '@';
            }
            else {
                $result .= rawurlencode($this->userName) . '@';
            }
        }

        if ($this->host !== null) {
            // We must percent-encode this, but not if it's IPv6
            $host = $this->host;
            if (strlen($host) && $host[0] !== '[' && $host[-1] !== ']') {
                $host = rawurlencode($this->host);
            }

            $result .= $host;
        }

        if ($this->port !== null) {
            $result .= ':' . $this->port;
        }

        $result .= $this->path;

        if ($this->queryComponent->count()) {
            $result .= '?' . $this->queryComponent->toString();
        }

        if (isset($this->fragment)) {
            $result .= '#' . rawurlencode($this->fragment);
        }

        return $result;
    }
}
