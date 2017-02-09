<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Expressions;

use Omines\Semver\Exception\SemverException;
use Omines\Semver\Version;

/**
 * PrimitiveGenerator.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class PrimitiveGenerator
{
    /** @var self */
    private static $instance;

    private $generators;

    protected function __construct()
    {
        $this->generators = [
            ExpressionParser::OPERATOR_CARET => [$this, 'generateCaretPrimitives'],
            ExpressionParser::OPERATOR_TILDE => [$this, 'generateTildePrimitives'],
            Primitive::OPERATOR_GT => [$this, 'generateGreaterThanPrimitives'],
            Primitive::OPERATOR_GE => [$this, 'generateGreaterThanOrEqualPrimitives'],
            Primitive::OPERATOR_LT => [$this, 'generateLessThanPrimitives'],
            Primitive::OPERATOR_LE => [$this, 'generateLessThanOrEqualPrimitives'],
            Primitive::OPERATOR_EQ => [$this, 'generateEqualsPrimitives'],
            Primitive::OPERATOR_NE => [$this, 'generateNotEqualsPrimitives'],
            Primitive::OPERATOR_NE_ALT => [$this, 'generateNotEqualsPrimitives'],
        ];
    }

    public function generate($operator, $data)
    {
        if (empty($operator)) {
            $operator = '=';
        }
        if (isset($this->generators[$operator])) {
            return forward_static_call_array($this->generators[$operator], $data);
        }
        throw SemverException::format('Unknown operator "%s"', $operator);
    }

    public static function getInstance()
    {
        return self::$instance ?: (self::$instance = new self());
    }

    public function generateCaretPrimitives(Version $lbound, array $ubound)
    {
        $realbound = $lbound->getNextSignificant();
        if (!empty($ubound)) {
            $realbound = Version::highest($realbound, Version::fromString(implode('.', $ubound)));
        }
        return self::between($lbound, $realbound);
    }

    public function generateTildePrimitives(Version $lbound, array $ubound, array $nrs)
    {
        if (count($nrs) == 1) {
            $upper = Version::fromString($nrs[0] + 1);
        } else {
            ++$nrs[1];
            $upper = Version::fromString(implode('.', array_slice($nrs, 0, 2)));
        }
        return self::between($lbound, $ubound ? Version::highest($upper, Version::fromString(implode('.', $ubound))) : $upper);
    }

    public function generateGreaterThanPrimitives(Version $lbound, array $ubound)
    {
        return [new Primitive($ubound ? implode('.', $ubound) : $lbound, Primitive::OPERATOR_GT)];
    }

    public function generateGreaterThanOrEqualPrimitives(Version $lbound)
    {
        return [new Primitive($lbound, Primitive::OPERATOR_LT, true)];
    }

    public function generateLessThanPrimitives(Version $lbound)
    {
        return [new Primitive($lbound, Primitive::OPERATOR_LT)];
    }

    public function generateLessThanOrEqualPrimitives(Version $lbound, array $ubound)
    {
        return [new Primitive($ubound ? implode('.', $ubound) : $lbound, Primitive::OPERATOR_GT, true)];
    }

    public function generateEqualsPrimitives(Version $lbound, array $ubound)
    {
        return empty($ubound) ? [new Primitive($lbound, Primitive::OPERATOR_EQ)] : self::between($lbound, implode('.', $ubound));
    }

    public function generateNotEqualsPrimitives(Version $lbound, array $ubound)
    {
        if (!empty($ubound)) {
            throw new SemverException('Inequality operator requires exact version');
        }
        return [new Primitive($lbound, Primitive::OPERATOR_EQ, true)];
    }

    /**
     * @param Version|string $lower
     * @param Version|string $upper
     * @return Primitive[] two primitives marking the non-inclusive range
     */
    private function between($lower, $upper)
    {
        return [
            new Primitive($lower, Primitive::OPERATOR_LT, true),
            new Primitive($upper, Primitive::OPERATOR_LT),
        ];
    }
}
