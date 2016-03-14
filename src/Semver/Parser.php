<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver;

use Omines\Semver\Exception\SemverException;
use Omines\Semver\Ranges\Primitive;

/**
 * Parser.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Parser
{
    const SECTION_VERSION = 'version';
    const SECTION_PRERELEASE = 'prerelease';
    const SECTION_BUILD = 'build';

    const REGEX_HYPHEN = '#^\s*([^\s]+)\s+\-\s+([^\s]+)\s*$#';
    const REGEX_RANGE = '#^\s*(\^|~|([><]?=?))([\dxX\*\.]+)(\-([a-z0-9\.\-]+))?\s*$#i';
    const REGEX_SEMVER2 = '#^[=v\s]*([\d\.]+)(\-([a-z0-9\.\-]+))?(\+([a-z0-9\.]+))?\s*$#i';
    const REGEX_SPLIT_RANGESET = '#\s*\|{1,2}\s*#';

    /**
     * @param string $version
     * @return array[] Array of arrays containing the separate sections.
     */
    public static function parseSemver2($version)
    {
        // Extract into separate parts
        if (!preg_match(self::REGEX_SEMVER2, $version, $matches)) {
            throw new SemverException(sprintf('Could not parse Semver2 string "%s"', $version));
        }

        // Parse version part
        $numbers = array_pad(array_map(function ($element) {
            if (!ctype_digit($element)) {
                throw new SemverException(sprintf('"%s" is not a valid version element', $element));
            }
            return (int) $element;
        }, explode('.', $matches[1])), 3, 0);
        if (count($numbers) > 3) {
            throw new SemverException(sprintf('Semver string "%s" contains %d version numbers, should be 3 at most', $version, count($numbers)));
        }

        // Parse prerelease and build parts
        return [
            self::SECTION_VERSION => $numbers,
            self::SECTION_PRERELEASE => isset($matches[3]) ?  self::splitSemver2Metadata($matches[3]) : [],
            self::SECTION_BUILD => isset($matches[5]) ? self::splitSemver2Metadata($matches[5]) : [],
        ];
    }

    private static function splitSemver2Metadata($metadata)
    {
        if (!isset($metadata) || 0 === strlen($metadata)) {
            return [];
        }

        return array_map(function ($element) {
            return ctype_digit($element) ? (int) $element : $element;
        }, explode('.', $metadata));
    }

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
            $nrs = explode('.', $parts[2]);
            if (count($nrs) < 3) {
                ++$nrs[count($nrs) - 1];
                $ubound = new Primitive(implode('.', $nrs), Primitive::OPERATOR_LT);
            } else {
                $ubound = new Primitive($parts[2], Primitive::OPERATOR_GT, true);
            }
            return [ new Primitive($parts[1], Primitive::OPERATOR_LT, true), $ubound ];
        }

        // Split regular simple constraints
        $primitives = [];
        foreach (preg_split('/\s+/', $range) as $simple) {
            $primitives += self::parseSimpleRange($simple);
        }
        return $primitives;
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
        $partial = str_replace(['*', 'x', 'X'], '*', $parts[3]);
        $qualifier = count($parts) > 4 ? $parts[4] : '';
        if (0 === ($wildcard = array_search('*', $xrs = explode('.', $partial)))) {
            return [new Primitive(Version::fromString('0'), Primitive::OPERATOR_LT, true)];
        } elseif ($wildcard) {
            $xrs = array_slice($xrs, 0, $wildcard);
        } elseif (count($xrs) < 3) {
            $wildcard = count($xrs);
        }
        $low = $high = array_pad($xrs, 3, 0);
        if ($wildcard) {
            ++$high[$wildcard - 1];
        }
        $version = implode('.', $low).$qualifier;
        $upper = implode('.', $high);

        switch ($parts[1] ?: '=') {
            case '>':
                return [new Primitive($version, Primitive::OPERATOR_GT)];
            case '<':
                return [new Primitive($version, Primitive::OPERATOR_LT)];
            case '>=':
                return [new Primitive($version, Primitive::OPERATOR_LT, true)];
            case '<=':
                return [new Primitive($version, Primitive::OPERATOR_GT, true)];
            case '=':
                if ($wildcard) {
                    return [
                        new Primitive($version, Primitive::OPERATOR_LT, true),
                        new Primitive($upper, Primitive::OPERATOR_LT),
                    ];
                }
                return [new Primitive($version, Primitive::OPERATOR_EQ)];
            case '!=':
            case '<>':
                if ($wildcard) {
                    return [
                        new Primitive($version, Primitive::OPERATOR_LT),
                        new Primitive($upper, Primitive::OPERATOR_GT),
                    ];
                }
                return [new Primitive($version, Primitive::OPERATOR_EQ, true)];
            case '^':
                $version = Version::fromString($version);
                $upper = Version::greatest($version->getNextSignificant(), Version::fromString($upper));
                return [
                    new Primitive($version, Primitive::OPERATOR_LT, true),
                    new Primitive($upper, Primitive::OPERATOR_LT),
                ];
            case '~':
                if (count($xrs) == 1) {
                    $upper = Version::fromString($xrs[0]+1);
                } else {
                    ++$xrs[1];
                    $upper = Version::fromString(implode('.', array_slice($xrs, 0, 2)));
                }
                return [
                    new Primitive($version, Primitive::OPERATOR_LT, true),
                    new Primitive($upper, Primitive::OPERATOR_LT),
                ];
            default:
                throw new \RuntimeException($parts[1]);
        }
    }
}
