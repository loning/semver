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
 * RangeParser
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class RangeParser
{
    const REGEX_HYPHEN = '#^\s*([^\s]+)\s+\-\s+([^\s]+)\s*$#';
    const REGEX_RANGE = '#^\s*(\^|~|!=|<>|([><]?=?))([\dxX\*\.]+)(\-([a-z0-9\.\-]+))?\s*$#i';
    const REGEX_SPLIT_RANGESET = '#\s*\|{1,2}\s*#';

    const OPERATOR_CARET = '^';
    const OPERATOR_TILDE = '~';

    /**
     * @param string $range
     * @return Primitive[][] Disjunctive collection of conjunctive collections of primitives.
     */
    public static function parseRangeSet($range)
    {
        // Split disjunctive elements
        $elements = preg_split(self::REGEX_SPLIT_RANGESET, trim($range));
        foreach ($elements as &$element) {
            $element = self::parseRange($element);
        }
        return $elements;
    }

    /**
     * @param string $range
     * @return Primitive[] Collection of primitives matching the range.
     */
    public static function parseRange($range)
    {
        // Detect hyphen
        if (preg_match(self::REGEX_HYPHEN, $range, $parts)) {
            return self::parseHyphen($parts[1], $parts[2]);
        }

        // Split regular simple constraints
        $primitives = [];
        foreach (preg_split('/\s+/', $range) as $simple) {
            $primitives += self::parseSimpleRange($simple);
        }
        return $primitives;
    }

    /**
     * @param string $from
     * @param string $to
     * @return Primitive[]
     */
    public static function parseHyphen($from, $to)
    {
        $nrs = explode('.', $to);
        if (count($nrs) < 3) {
            ++$nrs[count($nrs) - 1];
            $ubound = new Primitive(implode('.', $nrs), Primitive::OPERATOR_LT);
        } else {
            $ubound = new Primitive($to, Primitive::OPERATOR_GT, true);
        }
        return [ new Primitive($from, Primitive::OPERATOR_LT, true), $ubound ];
    }

    /**
     * @param string $simple
     * @return Primitive[] Collection of primitives matching the simple range.
     */
    public static function parseSimpleRange($simple)
    {
        if (!preg_match(self::REGEX_RANGE, $simple ?: '*', $parts)) {
            throw new SemverException(sprintf('Could not parse simple constraint "%s"', $simple));
        }
        $operator = $parts[1] ?: '=';
        $partial = str_replace(['*', 'x', 'X'], '*', $parts[3]);
        $qualifier = count($parts) > 4 ? $parts[4] : '';

        // Shortwire leading wildcard into the universal wildcard
        if($partial[0] === '*') {
            return [Primitive::getWildcard()];
        }

        list($lbound, $ubound, $nrs) = self::splitXr($partial, $qualifier);
        switch ($operator) {
            case self::OPERATOR_CARET:
                return self::parseCaret($lbound, $ubound);
            case self::OPERATOR_TILDE:
                return self::parseTilde($lbound, $ubound, $nrs);
            case Primitive::OPERATOR_GE:
            case Primitive::OPERATOR_LT:
                return [Primitive::fromParts($lbound, $operator)];
            case Primitive::OPERATOR_GT:
            case Primitive::OPERATOR_LE:
                return [Primitive::fromParts($ubound ? implode('.', $ubound) : $lbound, $operator)];
            case Primitive::OPERATOR_EQ:
                if ($ubound > 0) {
                    return self::between($lbound, implode('.', $ubound));
                }
                return [new Primitive($lbound, Primitive::OPERATOR_EQ)];
            case Primitive::OPERATOR_NE:
            case Primitive::OPERATOR_NE_ALT:
                if ($ubound) {
                    throw new SemverException('Inequality operator requires exact version');
                }
                return [Primitive::fromParts($lbound, Primitive::OPERATOR_NE)];
        }

        // @codeCoverageIgnoreStart
        throw new SemverException(sprintf('Unknown operator "%s"', $operator));
        // @codeCoverageIgnoreEnd
    }

    public static function parseCaret(Version $lbound, array $ubound = null)
    {
        if (isset($ubound)) {
            $ubound = Version::highest(
                $lbound->getNextSignificant(),
                Version::fromString(implode('.', $ubound))
            );
        } else {
            $ubound = $lbound->getNextSignificant();
        }
        return self::between($lbound, $ubound);
    }

    public static function parseTilde(Version $lbound, array $ubound = null, array $nrs)
    {
        if (count($nrs) == 1) {
            $upper = Version::fromString($nrs[0]+1);
        } else {
            ++$nrs[1];
            $upper = Version::fromString(implode('.', array_slice($nrs, 0, 2)));
        }
        if (isset($ubound)) {
            $upper = Version::highest($upper, Version::fromString(implode('.', $ubound)));
        }
        return self::between($lbound, $upper);
    }

    /**
     * @param string $partial
     * @param string $qualifier
     * @return array An array consisting of the base version, an array of NRs as the ubound, and the NRs.
     */
    private static function splitXr($partial, $qualifier = '')
    {
        $xrs = explode('.', $partial);
        if ($wildcard = array_search('*', $xrs)) {
            $xrs = array_slice($xrs, 0, $wildcard);
        } elseif (count($xrs) < 3) {
            $wildcard = count($xrs);
        } else {
            return [Version::fromString($partial . $qualifier), null, $xrs];
        }
        $low = $high = array_pad($xrs, 3, 0);
        ++$high[$wildcard - 1];
        return [Version::fromString(implode('.', $low) . $qualifier), $high, $xrs];
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
