<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Tests\Semver\Collections;

use Omines\Semver\Collections\VersionList;
use Omines\Semver\Version;

/**
 * VersionListTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 * @covers \Omines\Semver\Collections\AbstractVersionCollection
 * @covers \Omines\Semver\Collections\AbstractVersionIterator
 * @covers \Omines\Semver\Collections\VersionList
 * @covers \Omines\Semver\Collections\VersionListIterator
 */
class VersionListTest extends \PHPUnit\Framework\TestCase
{
    private $sorted;
    private $random;
    private $reverse;
    private $normalizedSorted;
    private $normalizedReverse;

    protected function setUp()
    {
        $this->sorted = json_decode(file_get_contents(FIXTURES_PATH . '/Semver2/IncrementalVersions.json'));
        $this->random = json_decode(file_get_contents(FIXTURES_PATH . '/Semver2/ShuffledVersions.json'));
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
        $list->each(function (Version $version) {
            /** @var Version $last */
            static $last;
            if ($last) {
                $this->assertTrue($last->lessThan($version));
            }
            $last = $version;
        });

        $list->rsort();
        $this->assertEquals($this->normalizedReverse, $list->getStringValues());
        $this->assertEquals($this->normalizedReverse[3], $list[3]);
        $list->each(function (Version $version) {
            /** @var Version $last */
            static $last;
            if ($last) {
                $this->assertTrue($last->greaterThanOrEqual($version));
            }
            $last = $version;
        });

        // Iterable behaviours
        foreach ($list as $idx => $version) {
            $this->assertEquals($this->normalizedReverse[$idx], (string) $version);
        }

        // Count/unset behaviours on arrays
        $this->assertEquals(count($this->sorted), count($list));
        $sampleKey = (int) (count($this->sorted) / 2);
        $this->assertTrue(isset($list[$sampleKey]));
        unset($list[$sampleKey]);
        $this->assertEquals(count($this->sorted) - 1, count($list));

        $before = $list->getStringValues();
        $list[3] = Version::fromString('6.8.4');
        $before[3] = '6.8.4';
        $this->assertSame($before, $list->getStringValues());
    }

    public function testVersionListArrayAccessors()
    {
        $list = VersionList::fromArray($this->sorted);
        $first = $list->getStringValues();
        $second = $list->getValues();

        foreach ($second as &$item) {
            $item = $item->getNormalizedString();
        }
        $this->assertSame($first, $second);
    }
}
