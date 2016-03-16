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
            self::VERSION => $numbers,
            self::PRERELEASE => isset($matches[3]) ?  self::splitMetadata($matches[3]) : [],
            self::BUILD => isset($matches[5]) ? self::splitMetadata($matches[5]) : [],
            self::COMPLIANCE => Version::COMPLIANCE_SEMVER2,
        ];
    }

    public static function parseLoose($version)
    {
        $numbers = $pre = $build = [];

        foreach (preg_split('/[\.\-]/', $version) as $element) {
            if (ctype_digit($element)) {
                if (count($numbers) < 4) {
                    $numbers[] = (int) $element;
                } else {
                    $pre[] = (int) $element;
                }
            } else {
                $pre[] = $element;
            }
        }
        if (empty($numbers)) {
            throw new SemverException(sprintf('No usable version numbers detected in "%s"', $version));
        }
        return [
            self::VERSION => $numbers,
            self::PRERELEASE => $pre,
            self::BUILD => $build,
            self::COMPLIANCE => Version::COMPLIANCE_NONE,
        ];
    }

    private static function splitMetadata($metadata)
    {
        if (!isset($metadata) || 0 === strlen($metadata)) {
            return [];
        }

        return array_map(function ($element) {
            return ctype_digit($element) ? (int) $element : $element;
        }, explode('.', $metadata));
    }
}
