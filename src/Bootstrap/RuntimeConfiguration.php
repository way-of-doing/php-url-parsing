<?php

namespace WayOfDoing\PhpUrlParsing\Bootstrap;

use WayOfDoing\PhpUrlParsing\Output\FormatterInterface;
use WayOfDoing\PhpUrlParsing\Uri\Parser\UriParserInterface;

/**
 * Immutable value object to hold the results of choices made by user input, in the form of business objects.
 */
class RuntimeConfiguration
{
    /**
     * @var string
     */
    private $urlToParse;

    /**
     * @var UriParserInterface
     */
    private $uriParser;

    /**
     * @var FormatterInterface
     */
    private $outputFormatter;

    /**
     * @param string $urlToParse
     * @param UriParserInterface $urlParser
     * @param FormatterInterface $outputFormatter
     */
    public function __construct(string $urlToParse, UriParserInterface $urlParser, FormatterInterface $outputFormatter)
    {
        $this->urlToParse = $urlToParse;
        $this->uriParser = $urlParser;
        $this->outputFormatter = $outputFormatter;
    }

    /**
     * @return string
     */
    public function getUrlToParse(): string
    {
        return $this->urlToParse;
    }

    /**
     * @return UriParserInterface
     */
    public function getUriParser(): UriParserInterface
    {
        return $this->uriParser;
    }

    /**
     * @return FormatterInterface
     */
    public function getOutputFormatter(): FormatterInterface
    {
        return $this->outputFormatter;
    }
}
