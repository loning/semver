<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests\Version;

use Omines\Semver\Segments\IdentifierSegment;
use Omines\Semver\Segments\NumbersSegment;

/**
 * SegmentTest
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class SegmentTest extends \PHPUnit_Framework_TestCase
{
    public function testSegments()
    {
        $segment = new IdentifierSegment('aap.noot.684.mies');
        $this->assertTrue(isset($segment[3]));
        $this->assertFalse(isset($segment[7]));

        $this->assertCount(4, $segment);
        $this->assertNull($segment[8]);
        $this->assertInternalType('int', $segment[2]);
        $this->assertInternalType('string', $segment[1]);

        unset($segment[1]);
        $this->assertCount(3, $segment);
        $this->assertEquals('mies', $segment[2]);

        $segment[] = 'burp';
        $this->assertEquals('aap.684.mies.burp', $segment);
        $this->assertCount(4, $segment);

        $segment[2] = 'noot';
        $this->assertEquals('aap.684.noot.burp', $segment);
        $this->assertCount(4, $segment);
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage is not a valid version segment
     */
    public function testInvalidIdentifier()
    {
        new IdentifierSegment('fwei.9299.jowrjkio.92*(U((N@N*');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage is not a numeric version segment value
     */
    public function testInvalidNumber()
    {
        new NumbersSegment('1.2.684.aap');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage is not a valid version segment
     */
    public function testInvalidSetter()
    {
        $segment = new IdentifierSegment();
        $segment[3] = '92*(U((N@N*';
    }
}
