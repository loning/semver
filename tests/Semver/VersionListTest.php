<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;

use Omines\Semver\Collections\VersionList;
use Omines\Semver\Version;

/**
 * VersionListTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class VersionListTest extends \PHPUnit_Framework_TestCase
{
    private $sorted;
    private $random;
    private $reverse;
    private $normalizedSorted;
    private $normalizedReverse;

    protected function setUp()
    {
        $this->sorted = json_decode(file_get_contents(__DIR__.'/Data/Semver2/IncrementalVersionsData.json'));
        $this->random = json_decode(file_get_contents(__DIR__.'/Data/Semver2/ShuffledVersionsData.json'));
        $this->reverse = array_reverse($this->sorted);

        $this->normalizedSorted = array_map(function ($item) {
            return Version::fromString($item)->getNormalizedString();
        }, $this->sorted);
        $this->normalizedReverse = array_reverse($this->normalizedSorted);
    }

    public function testVersionList()
    {
        $list = new VersionList();
        foreach ($this->random as $item) {
            $list[] = $item;
        }
        $list->sort();
        $this->assertEquals($this->normalizedSorted, $list->getStringValues());
        $this->assertEquals($this->normalizedSorted[5], $list[5]);
        $list->rsort();
        $this->assertEquals($this->normalizedReverse, $list->getStringValues());
        $this->assertEquals($this->normalizedReverse[3], $list[3]);

        // Iterable behaviours
        foreach ($list as $idx => $version) {
            $this->assertEquals($this->normalizedReverse[$idx], (string) $version);
        }

        // Count/unset behaviours on arrays
        $this->assertEquals(count($this->sorted), count($list));
        $sampleKey = (int) (count($this->sorted) / 2);
        $this->assertTrue(isset($list[$sampleKey]));
        unset($list[$sampleKey]);
        $this->assertFalse(isset($list[$sampleKey]));
        $this->assertEquals(count($this->sorted) - 1, count($list));

        $before = $list->getStringValues();
        $list[3] = Version::fromString('6.8.4');
        $before[3] = '6.8.4';
        $this->assertSame($before, $list->getStringValues());
    }

    public function testVersionListArrayAccessors()
    {
        $this->markTestIncomplete('Working on it');

        $list = VersionList::fromArray($this->sorted);
        $first = $list->toStringArray();
        $second = $list->toArray();

        foreach ($second as &$item) {
            $item = $item->toNormalizedString();
        }
        $this->assertSame($first, $second);
    }
}
