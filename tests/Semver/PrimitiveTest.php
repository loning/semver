<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;

use Omines\Semver\Parser\RangeParser;
use Omines\Semver\Parser\VersionParser;
use Omines\Semver\Ranges\Primitive;
use Symfony\Component\Yaml\Parser;

/**
 * PrimitiveTest
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class PrimitiveTest extends \PHPUnit_Framework_TestCase
{
    public function testAltInequality()
    {
        $primitive = Primitive::fromParts('1.0.0', '<>');
        $this->assertTrue($primitive->satisfiedBy('1.2.3'));
        $this->assertFalse($primitive->satisfiedBy('1.0.0'));
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Invalid primitive operator "invalid"
     */
    public function testInvalidOperatorThrows()
    {
        $primitive = new Primitive('1.0.0', 'invalid');
        $primitive->satisfiedBy('1.2.0');
    }

    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Invalid primitive operator "invalid
     */
    public function testInvalidPartThrows()
    {
        Primitive::fromParts('1.0.0', 'invalid');
    }
}
