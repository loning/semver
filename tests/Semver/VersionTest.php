<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;

use Omines\Semver\Version;

/**
 * SemverTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class VersionTest extends \PHPUnit_Framework_TestCase
{
    public function testValidCompleteSemverHandling()
    {
        $sample = '1.2.3-beta.1+build.25122015.ci';

        $semver = Version::fromString($sample);
        $this->assertSame($sample, $semver->getOriginalString());
        $this->assertSame($sample, $semver->getNormalizedString());
        $this->assertSame($sample, (string) $semver);

        // Separate components
        $this->assertSame(1, $semver->getMajorVersion());
        $this->assertSame(2, $semver->getMinorVersion());
        $this->assertSame(3, $semver->getPatchVersion());
        $this->assertSame('beta.1', $semver->getPrereleaseString());
        $this->assertSame('build.25122015.ci', $semver->getBuildString());

        // Test strings and numerics are handled correctly
        $this->assertSame('beta', $semver->getPrereleaseElement(0));
        $this->assertSame(1, $semver->getPrereleaseElement(1));
        $this->assertNull($semver->getPrereleaseElement(2));
        $this->assertSame('build', $semver->getBuildElement(0));
        $this->assertSame(25122015, $semver->getBuildElement(1));
        $this->assertSame('ci', $semver->getBuildElement(2));
        $this->assertNull($semver->getBuildElement(3));
    }

    public function testValidIncompleteSemverHandling()
    {
        $this->assertEquals('0.0.0', Version::fromString('0'));
        $this->assertEquals('1.0.0', Version::fromString('1'));
        $this->assertEquals('0.1.0', Version::fromString('0.1'));
        $this->assertEquals('0.1.0', Version::fromString('0.1'));
        $this->assertEquals('0.1.0', Version::fromString('0.1'));

        $this->assertEquals('1.0.0-beta.1', Version::fromString('1-beta.1'));
        $this->assertEquals('2.0.0+build.1', Version::fromString('2+build.1'));
        $this->assertEquals('3.0.0-alpha.1+build.684', Version::fromString('3-alpha.1+build.684'));
    }

    public function testPrereleasePreference()
    {
        $this->assertTrue(Version::fromString('1-alpha')->lessThan(Version::fromString('1')));
        $this->assertTrue(Version::fromString('1')->greaterThan(Version::fromString('1-rc')));
        $this->assertTrue(Version::fromString('1-beta')->lessThanOrEqual(Version::fromString('1')));
    }

    public function testComparisons()
    {
        $this->assertTrue(Version::fromString('3.0.0')->greaterThan(Version::fromString('2.3.4')));
        $this->assertTrue(Version::fromString('3.0.0')->greaterThanOrEqual(Version::fromString('2.3.4')));
        $this->assertTrue(Version::fromString('2.3.4')->greaterThanOrEqual(Version::fromString('2.3.4')));

        $this->assertTrue(Version::fromString('2.3.4')->lessThan(Version::fromString('2.3.5')));
        $this->assertTrue(Version::fromString('2.3.4')->lessThanOrEqual(Version::fromString('2.4.0')));
        $this->assertTrue(Version::fromString('2.3.4')->lessThanOrEqual(Version::fromString('2.3.4')));

        $this->assertTrue(Version::fromString('1')->equals(Version::fromString('1.0.0+build')));
        $this->assertFalse(Version::fromString('1.2.3')->equals(Version::fromString('0.1.2')));
    }

    /**
     * @dataProvider looseVersionsProvider
     */
    public function testLooseParsing($loose, $strict)
    {
        //$this->assertEquals($strict, Version::fromString($loose, Version::COMPLIANCE_NONE)->getNormalizedString());
        Version::fromString($loose, Version::COMPLIANCE_NONE)->getNormalizedString();
    }

    public function looseVersionsProvider()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/Data/Loose/Versions.json'), JSON_OBJECT_AS_ARRAY);
        foreach ($data as $key => $value) {
            yield $key => [$key, $value];
        }
    }

    public function testSemverIgnoresBuildData()
    {
        $this->assertSame(0, Version::fromString('1+build')->compare(Version::fromString('1+later')));
        $this->assertSame(0, Version::fromString('1+build.1.2.3')->compare(Version::fromString('1+build')));
    }

    public function testHighestLowest()
    {
        $versions = array_map([Version::class, 'fromString'], ['0.1.2', '1.2.3', '2.3.4', '3.4.5', '4.5.6']);
        $this->assertEquals('0.1.2', Version::lowest($versions)->getOriginalString());
        $this->assertEquals('4.5.6', Version::highest($versions)->getOriginalString());
        shuffle($versions);
        $this->assertEquals('0.1.2', Version::lowest($versions)->getOriginalString());
        $this->assertEquals('4.5.6', Version::highest($versions)->getOriginalString());
    }

    /**
     * @dataProvider comparisonProvider
     *
     * @param Version $low
     * @param Version $high
     */
    public function testSemverComparisons(Version $low, Version $high)
    {
        $this->assertLessThan(0, $low->compare($high));
        $this->assertGreaterThan(0, $high->compare($low));
    }

    public function comparisonProvider()
    {
        $versions = json_decode(file_get_contents(__DIR__ . '/Data/Semver2/IncrementalVersions.json'), JSON_OBJECT_AS_ARRAY);
        for ($i = 0; $i < count($versions) - 1; ++$i) {
            $low = $versions[$i];
            $high = $versions[$i + 1];
            yield "$low < $high" => [Version::fromString($low), Version::fromString($high)];
        }
    }
}
