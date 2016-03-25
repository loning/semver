<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Expressions;

use Omines\Semver\Exception\SemverException;
use Omines\Semver\Version;

/**
 * ExpressionParser
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class ExpressionParser
{
    const REGEX_HYPHEN = '#^\s*([^\s]+)\s+\-\s+([^\s]+)\s*$#';
    const REGEX_SIMPLE_EXPRESSION = '#^\s*(\^|~|!=|<>|([><]?=?))([\dxX\*\.]+)((\-([a-z0-9\.\-]+))|)\s*$#i';
    const REGEX_SPLIT_RANGESET = '#\s*\|\|?\s*#';

    const OPERATOR_CARET = '^';
    const OPERATOR_TILDE = '~';

    /**
     * @param string $expression
     * @return CompoundExpression
     */
    public static function parseExpression($expression)
    {
        // Split disjunctive elements
        $result = new CompoundExpression(CompoundExpression::DISJUNCTIVE);
        foreach (preg_split(self::REGEX_SPLIT_RANGESET, trim($expression)) as $element) {
            $result->add(self::parseConjunctiveExpression($element));
        }
        return $result;
    }

    /**
     * @param string $expression
     * @return CompoundExpression
     */
    public static function parseConjunctiveExpression($expression)
    {
        $result = new CompoundExpression(CompoundExpression::CONJUNCTIVE);
        if (preg_match(self::REGEX_HYPHEN, $expression, $parts)) {
            $result->addMultiple(self::parseHyphen($parts[1], $parts[2]));
        } else {
            foreach (preg_split('/\s+/', $expression) as $simple) {
                $result->addMultiple(self::parseSimpleExpression($simple));
            }
        }
        return $result;
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
     * @return Primitive[] Collection of primitives matching the simple expression.
     */
    public static function parseSimpleExpression($simple)
    {
        if (!preg_match(self::REGEX_SIMPLE_EXPRESSION, $simple ?: '*', $parts)) {
            throw SemverException::format('Could not parse simple constraint "%s"', $simple);
        }
        $operator = $parts[1];
        $partial = str_replace(['*', 'x', 'X'], '*', $parts[3]);
        $qualifier = $parts[4];

        // Redirect leading wildcard into the universal wildcard primitive right away
        if ($partial[0] === '*') {
            return [Primitive::getWildcard()];
        }
        return PrimitiveGenerator::getInstance()->generate($operator, self::expandXRs($partial, $qualifier));
    }

    /**
     * Splits a single partial into bounds if wildcards are included.
     *
     * @param string $partial
     * @param string $qualifier
     * @return array
     */
    private static function expandXRs($partial, $qualifier)
    {
        $xrs = explode('.', $partial);
        if ($wildcard = array_search('*', $xrs, true)) {
            $xrs = array_slice($xrs, 0, $wildcard);
        } elseif (count($xrs) < 3) {
            $wildcard = count($xrs);
        } else {
            return [Version::fromString($partial . $qualifier), [], $xrs];
        }
        $low = $high = array_pad($xrs, 3, 0);
        ++$high[$wildcard - 1];
        return [Version::fromString(implode('.', $low) . $qualifier), $high, $xrs];
    }
}
