<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Tests\Semver;

use Omines\Semver\Expressions\ExpressionParser;
use Omines\Semver\Version\VersionParser;

/**
 * ParserTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class ParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param string $input
     * @param array  $output
     */
    public function testSemver2($input, $output)
    {
        $this->assertSame($output, VersionParser::parseSemver2($input));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        foreach (json_decode(file_get_contents(FIXTURES_PATH . '/Semver2/ParserTest.json'), JSON_OBJECT_AS_ARRAY) as $key => $value) {
            yield $key => [$key, $value];
        }
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function generateData()
    {
        $output = [];
        foreach (['1', '1.0', '1.0.0', '1-alpha.1', '1+build.2', '1-rc.3+build.3'] as $sample) {
            $output[$sample] = VersionParser::parseSemver2($sample);
        }
        file_put_contents(FIXTURES_PATH . '/ParserTestData.json', json_encode($output, JSON_PRETTY_PRINT));
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Could not parse Semver2 string "foo/bar"
     */
    public function testInvalidSemver2Exception()
    {
        VersionParser::parseSemver2('foo/bar');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage "" is not a valid version element
     */
    public function testBrokenSemver2VersionException()
    {
        VersionParser::parseSemver2('1..2..3');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Semver string "1.2.3.4" contains 4 version numbers, should be 3 at most
     */
    public function testTooLongSemver2Exception()
    {
        VersionParser::parseSemver2('1.2.3.4');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Could not parse simple constraint
     */
    public function testBrokenExpressionException()
    {
        ExpressionParser::parseExpression('test || for || exception');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Inequality operator requires exact version
     */
    public function testInvalidInequalityException()
    {
        ExpressionParser::parseExpression('!=1.x');
    }
}
