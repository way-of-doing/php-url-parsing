<?php

namespace WayOfDoing\PhpUrlParsing\Output;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Formatter that exports all values as JSON.
 */
final class JsonFormatter implements FormatterInterface
{
    /**
     * @var int The options to be used with {@link json_encode()} when formatting values.
     */
    private $options;

    /**
     * @param int $options The options to be used with {@link json_encode()} when formatting values.
     */
    public function __construct(int $options = 0)
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function outputValueOn($value, OutputInterface $output)
    {
        $output->writeln(json_encode($value, $this->options));
    }
}
