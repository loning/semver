<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Parser;

use Omines\Semver\Exception\SemverException;
use Omines\Semver\Ranges\Primitive;
use Omines\Semver\Version;

/**
 * PrimitiveGenerator
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class PrimitiveGenerator
{
    public static function generateCaretPrimitives(Version $lbound, array $ubound)
    {
        $realbound = $lbound->getNextSignificant();
        if (!empty($ubound)) {
            $realbound = Version::highest($realbound, Version::fromString(implode('.', $ubound)));
        }
        return self::between($lbound, $realbound);
    }

    public static function generateTildePrimitives(Version $lbound, array $ubound, array $nrs)
    {
        if (count($nrs) == 1) {
            $upper = Version::fromString($nrs[0] + 1);
        } else {
            ++$nrs[1];
            $upper = Version::fromString(implode('.', array_slice($nrs, 0, 2)));
        }
        return self::between($lbound, $ubound ? Version::highest($upper, Version::fromString(implode('.', $ubound))) : $upper);
    }

    public static function generateGreaterThanPrimitives(Version $lbound, array $ubound)
    {
        return [new Primitive($ubound ? implode('.', $ubound) : $lbound, Primitive::OPERATOR_GT)];
    }

    public static function generateGreaterThanOrEqualPrimitives(Version $lbound)
    {
        return [new Primitive($lbound, Primitive::OPERATOR_LT, true)];
    }

    public static function generateLessThanPrimitives(Version $lbound)
    {
        return [new Primitive($lbound, Primitive::OPERATOR_LT)];
    }

    public static function generateLessThanOrEqualPrimitives(Version $lbound, array $ubound)
    {
        return [new Primitive($ubound ? implode('.', $ubound) : $lbound, Primitive::OPERATOR_GT, true)];
    }

    public static function generateEqualsPrimitives(Version $lbound, array $ubound)
    {
        return empty($ubound) ? [new Primitive($lbound, Primitive::OPERATOR_EQ)] : self::between($lbound, implode('.', $ubound));
    }

    public static function generateNotEqualsPrimitives(Version $lbound, array $ubound)
    {
        if (!empty($ubound)) {
            throw new SemverException('Inequality operator requires exact version');
        }
        return [new Primitive($lbound, Primitive::OPERATOR_EQ, true)];
    }

    /**
     * @param Version|string $lower
     * @param Version|string $upper
     * @return Primitive[] Two primitives marking the non-inclusive range.
     */
    private static function between($lower, $upper)
    {
        return [
            new Primitive($lower, Primitive::OPERATOR_LT, true),
            new Primitive($upper, Primitive::OPERATOR_LT),
        ];
    }
}
