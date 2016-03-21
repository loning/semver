<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Version;

use Omines\Semver\Exception\SemverException;
use Omines\Semver\Version;

/**
 * VersionParser
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class VersionParser
{
    const VERSION = 'version';
    const PRERELEASE = 'prerelease';
    const BUILD = 'build';
    const COMPLIANCE = 'compliance';

    const REGEX_SEMVER2 = '#^[=v\s]*([\d\.]+)(\-([a-z0-9\.\-]+))?(\+([a-z0-9\.]+))?\s*$#i';

    /**
     * @param string $version
     * @param \Exception[] $issues
     * @return array|null
     */
    public static function parse($version, &$issues)
    {
        static $parsers = [
            [self::class, 'parseSemver2'],
            [self::class, 'parseLoose'],
        ];
        $issues = [];
        foreach ($parsers as $parser) {
            try {
                return $parser($version);
            } catch (\Exception $e) {
                $issues[] = $e;
            }
        }
        return null;
    }

    /**
     * @param string $version
     * @return array<string,array> Array of arrays containing the separate sections.
     */
    public static function parseSemver2($version)
    {
        // Extract into separate parts
        if (!preg_match(self::REGEX_SEMVER2, $version, $matches)) {
            throw SemverException::format('Could not parse Semver2 string "%s"', $version);
        }
        $matches = array_pad($matches, 6, '');

        // Parse prerelease and build parts
        return [
            self::COMPLIANCE => Version::COMPLIANCE_SEMVER2,
            self::VERSION => self::splitSemverNumbers($matches[1]),
            self::PRERELEASE => $matches[3],
            self::BUILD => $matches[5],
        ];
    }

    /**
     * @param string $string
     * @return mixed[]
     */
    private static function splitSemverNumbers($string)
    {
        // Parse version part
        $numbers = array_pad(array_map(function ($element) {
            if (!ctype_digit($element)) {
                throw SemverException::format('"%s" is not a valid version element', $element);
            }
            return (int) $element;
        }, explode('.', $string)), 3, 0);
        if (count($numbers) > 3) {
            throw SemverException::format('Semver string "%s" contains %d version numbers, should be 3 at most', $string, count($numbers));
        }
        return $numbers;
    }

    public static function parseLoose($version)
    {
        $numbers = $pre = $build = [];

        foreach (preg_split('/[\.\-]/', $version) as $element) {
            if (ctype_digit($element)) {
                if (empty($pre)) {
                    $numbers[] = (int) $element;
                } else {
                    $pre[] = (int) $element;
                }
            } else {
                $pre[] = $element;

            }
        }
        if (empty($numbers)) {
            throw SemverException::format('No usable version numbers detected in "%s"', $version);
        }
        return [
            self::COMPLIANCE => Version::COMPLIANCE_NONE,
            self::VERSION => $numbers,
            self::PRERELEASE => $pre,
            self::BUILD => $build,
        ];
    }
}
