<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Version;

/**
 * Stability.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Stability
{
    const ALPHA = 'alpha';
    const BETA = 'beta';
    const DEV = 'dev';
    const RC = 'rc';
    const STABLE = 'stable';

    private static $regexp;

    private static $precedence;

    private static $aliases;

    private $stability;

    public function __construct($string)
    {
        if (!self::$precedence) {
            self::initialize();
        }
    }

    private static function initialize()
    {
        self::$precedence = [
            self::DEV => 0,
            self::ALPHA => 1,
            self::BETA => 2,
            self::RC => 3,
            self::STABLE => 4,
        ];
        self::$aliases = [
            self::ALPHA => self::ALPHA,
            self::BETA => self::BETA,
            self::RC => self::RC,
            'a' => self::ALPHA,
            'b' => self::BETA,
            'r' => self::RC,
        ];
        self::$regexp = sprintf('#^(%s)(\d+)#i', implode('|', array_keys(self::$aliases)));
    }
}
