<?php

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use WayOfDoing\PhpUrlParsing\Bootstrap\BootstrapFactory;
use WayOfDoing\PhpUrlParsing\Bootstrap\InsufficientInputException;

require __DIR__ . '/../vendor/autoload.php';

try {
    $output = new ConsoleOutput();
    $factory = new BootstrapFactory(new ArgvInput());
    $config = $factory->getRuntimeConfiguration();

    $url = $config->getUriParser()->parseUri($config->getUrlToParse());

    // Just an arbitrary mapping of values to desired keys for the output
    $result = [
        'scheme'    => $url->getScheme(),
        'host'      => $url->getHost(),
        'port'      => $url->getPort(),
        'user'      => $url->getUserName(),
        'pass'      => $url->getPassword(),
        'path'      => $url->getPath(),
        'arguments' => $url->getQueryComponent()->toArray(),
        'fragment'  => $url->getFragment(),
    ];

    // Only show those keys that have non-null and non-empty-array values -- count() fits the bill perfectly here
    $result = array_filter($result, 'count');

    $config->getOutputFormatter()->outputValueOn($result, $output);
}
catch (InsufficientInputException $e) {
    $output->writeln(sprintf('usage: %s %s', basename(__FILE__), $e->getMessage()));
}
catch (Exception $e) {
    $errorOutput = $output->getErrorOutput();
    $errorOutput->write('error: ');
    $errorOutput->writeln($e->getMessage());
    exit(1);
}
