<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;

use Omines\Semver\Parser;

/**
 * ParserTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param string $input
     * @param array  $output
     */
    public function testSemver2($input, $output)
    {
        $this->assertSame($output, Parser::parseSemver2($input));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return json_decode(file_get_contents(__DIR__ . '/Data/Semver2/ParserTestData.json'), JSON_OBJECT_AS_ARRAY);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function generateData()
    {
        $output = [];
        foreach (['1', '1.0', '1.0.0', '1-alpha.1', '1+build.2', '1-rc.3+build.3'] as $sample) {
            $output[$sample] = [$sample, Parser::parseSemver2($sample)];
        }
        file_put_contents(__DIR__ . '/Data/ParserTestData.json', json_encode($output, JSON_PRETTY_PRINT));
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Could not parse Semver2 string "foo/bar"
     */
    public function testInvalidSemver2Exception()
    {
        Parser::parseSemver2('foo/bar');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage "" is not a valid version element
     */
    public function testBrokenSemver2VersionException()
    {
        Parser::parseSemver2('1..2..3');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Semver string "1.2.3.4" contains 4 version numbers, should be 3 at most
     */
    public function testTooLongSemver2Exception()
    {
        Parser::parseSemver2('1.2.3.4');
    }
}
