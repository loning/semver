<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;

use Omines\Semver\Collections\VersionMap;

/**
 * SemverMapTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class VersionMapTest extends \PHPUnit_Framework_TestCase
{
    public function testFillVersionMap()
    {
        $map = new VersionMap();
        $map['1.2'] = 1;
        $map['0.2.2'] = 2;
        $map['23.0.0-test'] = 3;
        $map['2.3.4+build.684'] = 4;
        $map['6.8.4-123+456'] = 5;
        $this->assertCount(5, $map);
        return $map;
    }

    /**
     * @depends clone testFillVersionMap
     */
    public function testVersionMapMaintainOrder($map)
    {
        $test = array();
        foreach ($map as $key => $value) {
            $test[] = $value;
        }
        $this->assertSame(array(1, 2, 3, 4, 5), $test);
    }

    /**
     * @depends clone testFillVersionMap
     */
    public function testModifyingVersionMapElements($map)
    {
        unset($map['0.2.2']);
        unset($map['6.8.4-123+456']);
        $map['3.4'] = 6;

        $test = array();
        foreach ($map as $key => $value) {
            $test[] = $value;
        }
        $this->assertSame(array(1, 3, 4, 6), $test);
    }

    /**
     * @depends testFillVersionMap
     */
    public function testSerialization($map)
    {
        $string = serialize($map);
        /** @var VersionMap $unserialized */
        $unserialized = unserialize($string);

        $this->assertCount(5, $unserialized);
        $this->assertSame(array(1, 2, 3, 4, 5), $unserialized->getValues());
    }
}
