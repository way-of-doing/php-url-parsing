<?php

namespace WayOfDoing\PhpUrlParsing\Output;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Formatter that exports all values as YAML.
 */
final class YamlFormatter implements FormatterInterface
{
    /**
     * @inheritdoc
     */
    public function outputValueOn($value, OutputInterface $output)
    {
        $output->writeln(Yaml::dump($value, 10));
    }
}
