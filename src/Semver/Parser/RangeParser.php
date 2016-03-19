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
    const REGEX_RANGE = '#^\s*(\^|~|!=|<>|([><]?=?))([\dxX\*\.]+)((\-([a-z0-9\.\-]+))|)\s*$#i';
    const REGEX_SPLIT_RANGESET = '#\s*\|\|?\s*#';

    const OPERATOR_CARET = '^';
    const OPERATOR_TILDE = '~';

    /** @var array<string|integer,object<Closure>> */
    private static $generators;

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
            $primitives = array_merge($primitives, self::parseSimpleRange($simple));
        }
        return $primitives;
    }

    /**
     * @param string $lower
     * @param string $upper
     * @return Primitive[]
     */
    public static function parseHyphen($lower, $upper)
    {
        $nrs = explode('.', $upper);
        if (count($nrs) < 3) {
            ++$nrs[count($nrs) - 1];
            $ubound = new Primitive(implode('.', $nrs), Primitive::OPERATOR_LT);
        } else {
            $ubound = new Primitive($upper, Primitive::OPERATOR_GT, true);
        }
        return [new Primitive($lower, Primitive::OPERATOR_LT, true), $ubound];
    }

    /**
     * @param string $simple
     * @return Primitive[] Collection of primitives matching the simple range.
     */
    public static function parseSimpleRange($simple)
    {
        if (!preg_match(self::REGEX_RANGE, $simple ?: '*', $parts)) {
            throw SemverException::format('Could not parse simple constraint "%s"', $simple);
        }
        $operator = $parts[1] ?: '=';
        $partial = str_replace(['*', 'x', 'X'], '*', $parts[3]);
        $qualifier = $parts[4];

        // Redirect leading wildcard into the universal wildcard primitive right away
        if ($partial[0] === '*') {
            return [Primitive::getWildcard()];
        }
        return self::generativePrimitives($operator, self::processXrs($partial, $qualifier));
    }

    private static function processXrs($partial, $qualifier)
    {
        $xrs = explode('.', $partial);
        if ($wildcard = array_search('*', $xrs, true)) {
            $xrs = array_slice($xrs, 0, $wildcard);
        } elseif (count($xrs) < 3) {
            $wildcard = count($xrs);
        } else {
            return [Version::fromString($partial . $qualifier), $xrs, []];
        }
        $low = $high = array_pad($xrs, 3, 0);
        ++$high[$wildcard - 1];
        return [Version::fromString(implode('.', $low) . $qualifier), $xrs, $high];
    }

    private static function generativePrimitives($operator, $data)
    {
        if (!isset(self::$generators)) {
            self::initGenerators();
        }
        if (is_callable(self::$generators[$operator])) {
            return forward_static_call_array(self::$generators[$operator], $data);
        }

        // @codeCoverageIgnoreStart
        throw SemverException::format('Unknown operator "%s"', $operator);
        // @codeCoverageIgnoreEnd
    }

    private static function initGenerators()
    {
        $generatorClass = PrimitiveGenerator::class;
        self::$generators = [
            self::OPERATOR_CARET => [$generatorClass, 'generateCaretPrimitives'],
            self::OPERATOR_TILDE => [$generatorClass, 'generateTildePrimitives'],
            Primitive::OPERATOR_GT => [$generatorClass, 'generateGreaterThanPrimitives'],
            Primitive::OPERATOR_GE => [$generatorClass, 'generateGreaterThanOrEqualPrimitives'],
            Primitive::OPERATOR_LT => [$generatorClass, 'generateLessThanPrimitives'],
            Primitive::OPERATOR_LE => [$generatorClass, 'generateLessThanOrEqualPrimitives'],
            Primitive::OPERATOR_EQ => [$generatorClass, 'generateEqualsPrimitives'],
            Primitive::OPERATOR_NE => [$generatorClass, 'generateNotEqualsPrimitives'],
            Primitive::OPERATOR_NE_ALT => [$generatorClass, 'generateNotEqualsPrimitives'],
        ];
    }
}
