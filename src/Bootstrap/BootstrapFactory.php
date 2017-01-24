<?php

namespace WayOfDoing\PhpUrlParsing\Bootstrap;

use LogicException;
use OutOfBoundsException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use WayOfDoing\PhpUrlParsing\Output\FormatterInterface;
use WayOfDoing\PhpUrlParsing\Output\JsonFormatter;
use WayOfDoing\PhpUrlParsing\Output\YamlFormatter;
use WayOfDoing\PhpUrlParsing\Uri\Parser\PhpInternalUriParser;
use WayOfDoing\PhpUrlParsing\Uri\Parser\Rfc3986NonValidatingParser;
use WayOfDoing\PhpUrlParsing\Uri\Parser\UriParserInterface;

/**
 * Takes care of checking the environment and translating user input to business objects.
 */
final class BootstrapFactory
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var RuntimeConfiguration|null Lazy-created runtime configuration cache.
     */
    private $runtimeConfiguration;

    /**
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Returns runtime configuration object that encapsulates the results of all choices driven by user input.
     *
     * @return RuntimeConfiguration
     */
    public function getRuntimeConfiguration(): RuntimeConfiguration
    {
        return $this->runtimeConfiguration
            ?? $this->runtimeConfiguration = $this->createRuntimeConfiguration();
    }

    /**
     * Actually creates the runtime configuration instance if not already cached.
     *
     * @return RuntimeConfiguration
     *
     * @throws \RuntimeException if parsing of user input fails.
     * @throws InsufficientInputException if no URL is provided by the user.
     */
    private function createRuntimeConfiguration(): RuntimeConfiguration
    {
        $inputDefinition = $this->createInputDefinition();
        $this->input->bind($inputDefinition); // can throw

        $urlToParse = $this->input->getArgument('url');

        // A small trick to display brief usage instructions if no input provided
        if (!strlen($urlToParse)) {
            throw new InsufficientInputException($inputDefinition->getSynopsis());
        }

        return new RuntimeConfiguration(
            $urlToParse,
            $this->createUrlParser($this->input),
            $this->createOutputFormatter($this->input)
        );
    }

    /**
     * Creates the symfony/console input definition for the program.
     *
     * @return InputDefinition
     */
    private function createInputDefinition(): InputDefinition
    {
        $inputDefinition = new InputDefinition();
        $inputDefinition->addOption(new InputOption('json', null, InputOption::VALUE_NONE, 'Format output as JSON'));
        $inputDefinition->addOption(new InputOption('parser', null, InputOption::VALUE_REQUIRED, 'Type of parser'));
        $inputDefinition->addArgument(new InputArgument('url', InputArgument::REQUIRED, 'Url to be parsed'));

        return $inputDefinition;
    }

    /**
     * Creates the URI parser instance to be used based on user input.
     *
     * @param InputInterface $input
     *
     * @return UriParserInterface
     *
     * @throws OutOfBoundsException if the parser has been incorrectly specified.
     * @throws LogicException if the specified parser mapping is messed up.
     */
    private function createUrlParser(InputInterface $input): UriParserInterface
    {
        $recognizedParsers = [
            'php' => PhpInternalUriParser::class,
            'rfc3986' => Rfc3986NonValidatingParser::class,
        ];

        $requestedParser = $input->getOption('parser') ?? 'php';

        if (!isset($recognizedParsers[$requestedParser])) {
            $message = sprintf(
                'Specified parser "%s" must be one of "%s"',
                $requestedParser,
                implode('", "', array_keys($recognizedParsers))
            );

            throw new OutOfBoundsException($message);
        }
        else if (!class_exists($recognizedParsers[$requestedParser])) {
            $message = sprintf(
                'Specified parser "%s" corresponds to non-existing class %s',
                $requestedParser,
                $recognizedParsers[$requestedParser]
            );

            throw new LogicException($message);
        }

        if (!is_a($recognizedParsers[$requestedParser], UriParserInterface::class, true)) {
            $message = sprintf(
                'Specified parser "%s" (implemented in %s) is not an instance of %s',
                $requestedParser,
                $recognizedParsers[$requestedParser],
                UriParserInterface::class
            );

            throw new LogicException($message);
        }

        return new $recognizedParsers[$requestedParser]();
    }

    /**
     * Creates output formatter instance based on user input.
     *
     * @param InputInterface $input
     *
     * @return FormatterInterface
     */
    private function createOutputFormatter(InputInterface $input): FormatterInterface
    {
        return $input->getOption('json') ? new JsonFormatter(JSON_PRETTY_PRINT) : new YamlFormatter();
    }
}
