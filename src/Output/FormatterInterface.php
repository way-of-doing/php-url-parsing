<?php

namespace WayOfDoing\PhpUrlParsing\Output;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Represents a value formatter.
 *
 * Value formatters are strategies that decide how to export arbitrary values to an arbitrary {@link OutputInterface}.
 */
interface FormatterInterface
{
    /**
     * Formats and exports the specified value to the specified output interface.
     *
     * @param mixed $value The value to be exported.
     * @param OutputInterface $output The output interface to export the value to.
     *
     * @return void
     */
    public function outputValueOn($value, OutputInterface $output);
}
