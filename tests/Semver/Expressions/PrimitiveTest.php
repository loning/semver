<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Tests\Semver\Expressions;

use Omines\Semver\Expressions\Primitive;
use Omines\Semver\Version;

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
        $primitive->matches(Version::fromString('1.2.0'));
    }
}
