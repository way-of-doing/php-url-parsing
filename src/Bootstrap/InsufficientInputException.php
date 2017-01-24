<?php

namespace WayOfDoing\PhpUrlParsing\Bootstrap;

use RuntimeException;

/**
 * Exception thrown if the user has not specified a URL to parse in the input.
 */
final class InsufficientInputException extends RuntimeException
{
}
