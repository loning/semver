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
        $xrs = explode('.', str_replace(['*', 'x', 'X'], '*', $parts[3]));
        $qualifier = count($parts) > 4 ? $parts[4] : '';

        if($xrs[0] === '*') {
            return [new Primitive(Version::fromString('0'), Primitive::OPERATOR_LT, true)];
        } elseif ($wildcard = array_search('*', $xrs)) {
            $xrs = array_slice($xrs, 0, $wildcard);
        } elseif (count($xrs) < 3) {
            $wildcard = count($xrs);
        }
        $low = $high = array_pad($xrs, 3, 0);
        if ($wildcard > 0) {
            ++$high[$wildcard - 1];
        }
        $version = implode('.', $low).$qualifier;
        $upper = implode('.', $high);

        switch ($operator) {
            case '>':
                return [new Primitive($version, Primitive::OPERATOR_GT)];
            case '<':
                return [new Primitive($version, Primitive::OPERATOR_LT)];
            case '>=':
                return [new Primitive($version, Primitive::OPERATOR_LT, true)];
            case '<=':
                return [new Primitive($version, Primitive::OPERATOR_GT, true)];
            case '=':
                if ($wildcard > 0) {
                    return self::between($version, $upper);
                }
                return [new Primitive($version, Primitive::OPERATOR_EQ)];
            case '!=':
            case '<>':
                if ($wildcard > 0) {
                    throw new SemverException('Inequality operator requires exact version');
                }
                return [new Primitive($version, Primitive::OPERATOR_EQ, true)];
            case '^':
                $version = Version::fromString($version);
                $upper = Version::highest($version->getNextSignificant(), Version::fromString($upper));
                return self::between($version, $upper);
            case '~':
                if (count($xrs) == 1) {
                    $upper = Version::fromString($xrs[0]+1);
                } else {
                    ++$xrs[1];
                    $upper = Version::fromString(implode('.', array_slice($xrs, 0, 2)));
                }
                return self::between($version, $upper);
            // @codeCoverageIgnoreStart
        }
        throw new SemverException('Unexpected operator ' . $parts[1]);
        // @codeCoverageIgnoreEnd
    }

    private static function between($lower, $upper)
    {
        return [
            new Primitive($lower, Primitive::OPERATOR_LT, true),
            new Primitive($upper, Primitive::OPERATOR_LT),
        ];
    }
}
