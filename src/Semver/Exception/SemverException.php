<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Exception;

/**
 * Base exception for Semver related issues.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class SemverException extends \RuntimeException
{
    /**
     * Utility function for easily formatting exceptions.
     *
     * @param string $string
     * @param mixed ...
     * @return self
     */
    public static function format($string)
    {
        $type = get_called_class();
        return new $type(vsprintf($string, array_slice(func_get_args(), 1)));
    }
}
