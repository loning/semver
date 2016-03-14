<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;

use Omines\Semver\Ranges\Primitive;

/**
 * PrimitiveTest
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class PrimitiveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Omines\Semver\Exception\SemverException
     * @expectedExceptionMessage Invalid primitive operator "invalid"
     */
    public function testInvalidOperatorThrows()
    {
        $primitive = new Primitive('1.0.0', 'invalid');
        $primitive->satisfiedBy('1.2.0');
    }
}
