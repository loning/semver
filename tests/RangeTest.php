<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;
use Omines\Semver\Ranges\Range;

/**
 * RangeTest
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class RangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider variousRangesProvider
     * @param string $range
     */
    public function testRangeParser($range)
    {
        $this->markTestIncomplete();

        $range = Range::fromString($range);
        var_dump($range->getNormalizedString());
    }

    public function variousRangesProvider()
    {
        $ranges = array(
            '=1.2.3',
            '>2.3.4',
            '<3.4.5',
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
            '1-2 | 4-5   ',
            '^1.2 | ~2.3 | ~3.4 | ^4.5',
            '^1.2 | ~2.3 | 3.4 - 3.5 | ^4.5',
        );
        return array_combine($ranges, array_map(function($item) { return array($item); }, $ranges));
    }

    /**
     * @dataProvider rangeDataProvider
     *
     * @param string $range
     * @param string $type
     * @param string $version
     */
    public function testRange($range, $type, $version)
    {
        $this->assertTrue(Range::fromString('^5.3.0') instanceof Range);
/*
        $this->markTestIncomplete();

        $range = new Range($range);

        $this->assertTrue(rand(0, 100) % 2); */
    }

    public function rangeDataProvider()
    {
        $result = array();
        $data = json_decode(file_get_contents(__DIR__.'/Data/Ranges/RangeData.json'), JSON_OBJECT_AS_ARRAY);
        foreach ($data as $range => $tests) {
            foreach ($tests as $type => $versions) {
                foreach ($versions as $version) {
                    $result["$range $type $version"] = array($range, $type, $version);
                }
            }
        }
        return $result;
    }
}
