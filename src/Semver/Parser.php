<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver;

use Omines\Semver\Exception\SemverException;

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

    /**
     * @param string $version
     * @return array[] Array of arrays containing the separate sections.
     */
    public static function parseSemver2($version)
    {
        // Extract into separate parts
        if (!preg_match('#^[=v\s]*([\d\.]+)(\-([a-z0-9\.\-]+))?(\+([a-z0-9\.]+))?\s*$#i', $version, $matches)) {
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
}
