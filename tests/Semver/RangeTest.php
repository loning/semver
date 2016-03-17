<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;

use Omines\Semver\Ranges\Range;
use Omines\Semver\Version;

/**
 * RangeTest
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class RangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider variousRangesProvider
     * @param string $string
     */
    public function testRangeParser($string)
    {
        $range = Range::fromString($string);
        $version = Version::fromString('6.8.4-alpha');

        $this->assertEquals($string, $range->getOriginalString());
        $this->assertEquals($range->satisfiedBy($version), $version->satisfies($range));
    }

    public function variousRangesProvider()
    {
        $ranges = [
            '^0.0.0',
            '^0.0.1',
            '^0.1.1',
            '~0.0.0',
            '~0.0.1',
            '~0.1.1',
            '=1.2.3',
            '>2.3.4',
            '<3.4.5',
            '!=4.5.6',
            '<>5.6.7',
            '>=4.5.6',
            '<=5.6.7',
            '~6.7.8',
            '^7.8.9',
            '1.2.3 - 2.3.4',
            '1.2 - 2.3',
            '1.2.x',
            '1.*',
            '2.X',
            '*',
            '1.2.3|2.3.4',
            ' 1.2.3 || 2.3.4',
            '1 - 2 | 4 - 5   ',
            '^1.2 | ~2.3 | ~3.4 | ^4.5',
            '^1.2 | ~2.3 | 3.4 - 3.5 | ^4.5',
        ];
        return array_combine($ranges, array_map(function ($item) { return [$item]; }, $ranges));
    }

    /**
     * @dataProvider rangeDataProvider
     *
     * @param string $string
     * @param string $type
     * @param Version $version
     */
    public function testRange($string, $type, $version)
    {
        $range = Range::fromString($string);
        switch ($type) {
            case 'match':
                $this->assertTrue($range->satisfiedBy($version));
                break;
            case 'fail':
                $this->assertFalse($range->satisfiedBy($version));
                break;
            default:
                // TODO: Implement lt/gt
        }
    }

    public function rangeDataProvider()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/Data/Ranges/RangeMatches.json'), JSON_OBJECT_AS_ARRAY);
        foreach ($data as $range => $tests) {
            foreach ($tests as $type => $versions) {
                foreach ($versions as $version) {
                    yield "$range $type $version" => [$range, $type, Version::fromString($version)];
                }
            }
        }
    }

    /**
     * @dataProvider normalizedRangeDataProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testRangeNormalization($input, $expected)
    {
        $this->assertEquals($expected, $range = Range::fromString($input));
        $this->assertEquals($expected, Range::fromString($range)->getNormalizedString());
    }

    public function normalizedRangeDataProvider()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/Data/Ranges/NormalizedRanges.json'), JSON_OBJECT_AS_ARRAY);
        foreach ($data as $key => $value) {
            yield $value => [$key, $value];
        }
    }
}
