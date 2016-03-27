<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Semver\Expressions;

use Omines\Semver\Expressions\CompoundExpression;
use Omines\Semver\Expressions\Primitive;
use Omines\Semver\Version;

/**
 * CompoundExpressionTest
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 * @covers Omines\Semver\Expressions\CompoundExpression
 */
class CompoundExpressionTest extends \PHPUnit_Framework_TestCase
{
    public function testCompoundExpression()
    {
        $expression = new CompoundExpression(CompoundExpression::DISJUNCTIVE, [
            new Primitive('1.0.0', Primitive::OPERATOR_EQ),
            '>2.3.4',
        ]);
        $this->assertTrue($expression->matches(Version::fromString('1.0.0')));
        $this->assertTrue($expression->matches(Version::fromString('3.0.0')));
        $this->assertTrue($expression->matches(Version::fromString('2.3.5')));
        $this->assertFalse($expression->matches(Version::fromString('1.0.0-alpha')));
        $this->assertFalse($expression->matches(Version::fromString('2.3.4')));
        $this->assertFalse($expression->matches(Version::fromString('0.9.9')));
    }
}
