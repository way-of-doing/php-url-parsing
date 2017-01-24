<?php

namespace WayOfDoing\PhpUrlParsing\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * End-to-end test that exercises the project binary and inspects its output.
 */
class ParserTest extends TestCase
{
    /**
     * @var string Constant that represents standard output; value should be human-readable.
     */
    const STDOUT = 'STDOUT';

    /**
     * @var string Constant that represents standard error; value should be human-readable.
     */
    const STDERR = 'STDERR';

    /**
     * @var string The full path to the project binary, cached.
     */
    private static $parserBinaryPath;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $projectRootDirectory = dirname(dirname(dirname(__FILE__)));
        $relativeBinaryPath = $projectRootDirectory . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'parser.php';
        self::$parserBinaryPath = realpath($relativeBinaryPath);

        if (self::$parserBinaryPath === false) {
            throw new \Exception("Parser binary $relativeBinaryPath not found");
        }
    }

    /**
     * Tests that the project binary produces input and exit codes according to the spec.
     *
     * Note that this test does not need to exercise multiple parsers, as the unit tests for those guarantee output
     * will be as expected for any particular input URI.
     *
     * @param string[] $arguments Command line arguments for the process.
     * @param bool $expectedSuccess True if the process was expected to complete successfully; otherwise, false.
     * @param null|bool|string $stdOutResult If bool, STDOUT must (true) or must not (false) have any output.
     *  If a string, the output must exactly match the specified string. If null, no checks are performed.
     * @param null|bool|string $stdErrResult If bool, STDERR must (true) or must not (false) have any output.
     *  If a string, the output must exactly match the specified string. If null, no checks are performed.
     *
     * @dataProvider outputDataProvider
     */
    public function testOutput(array $arguments, bool $expectedSuccess, $stdOutResult = null, $stdErrResult = null)
    {
        array_unshift($arguments, self::$parserBinaryPath);
        $arguments = implode(' ', array_map('escapeshellarg', $arguments));
        $process = new Process('php ' . $arguments);
        $process->run();

        $actualSuccess = $process->isSuccessful();
        $this->assertSame($expectedSuccess, $actualSuccess);

        $this->assertOutputResult($stdOutResult, $process, self::STDOUT);
        $this->assertOutputResult($stdErrResult, $process, self::STDERR);
    }

    public function outputDataProvider()
    {
        yield 'no arguments, should print usage' => [[], true, true, false];

        yield 'olx example #1' => [['https://www.google.com/?q=OLX&lang=de'], true, true, false];

        yield 'olx example #2' => [['--json', 'https://www.google.com/?q=OLX&lang=de'], true, true, false];

        yield 'olx example #3' => [['--json', '/www.google.com/?q=OLX&lang=de'], true, true, false];

        yield 'olx example #4' => [['--json', 'http://?'], false, false, true];

        $expectedOutput = json_encode(['path' => 'foo'], JSON_PRETTY_PRINT) . PHP_EOL;
        yield 'test json output' => [['--json', 'foo'], true, $expectedOutput, false];

        $expectedOutput = Yaml::dump(['path' => 'foo']) . PHP_EOL;
        yield 'test yaml output' => [['foo'], true, $expectedOutput, false];
    }

    private function assertOutputResult($expected, $process, $outputStream)
    {
        $outputGetter = $outputStream === self::STDERR ? 'getErrorOutput' : 'getOutput';
        $outputResult = $process->$outputGetter();

        if (is_string($expected)) {
            $this->assertSame($expected, $outputResult);
        }
        else if (is_bool($expected)) {
            $assertion = $expected ? 'assertNotSame' : 'assertSame';
            $this->$assertion(
                '',
                $outputResult,
                sprintf(
                    "Failed asserting that the parser %s generate output on %s.",
                    $expected ? 'DID' : 'DID NOT',
                    $outputStream
                )
            );
        }
    }
}
