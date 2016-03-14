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
     * @param string $version
     */
    public function testRange($string, $type, $version)
    {
        $range = Range::fromString($string);
        $this->assertEquals($string, $range->getOriginalString());
    }

    public function rangeDataProvider()
    {
        $result = [];
        $data = json_decode(file_get_contents(__DIR__ . '/Data/Ranges/RangeMatches.json'), JSON_OBJECT_AS_ARRAY);
        foreach ($data as $range => $tests) {
            foreach ($tests as $type => $versions) {
                foreach ($versions as $version) {
                    $result["$range $type $version"] = [$range, $type, $version];
                }
            }
        }
        return $result;
    }

    /**
     * @dataProvider normalizedRangeDataProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testRangeNormalization($input, $expected)
    {
        $this->assertEquals($expected, Range::fromString($input));
    }

    public function normalizedRangeDataProvider()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/Data/Ranges/NormalizedRanges.json'), JSON_OBJECT_AS_ARRAY);
        foreach ($data as $key => &$value) {
            $value = [$key, $value];
        }
        return $data;
    }
}
