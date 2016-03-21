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

/**
 * Segment
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class IdentifierSegment extends AbstractSegment
{
    const REGEX_IDENTIFIER_ELEMENT = '#^[A-Za-z0-9\-]+$#';

    /**
     * @param mixed $first
     * @param mixed $second
     * @return int
     */
    protected function compareElements($first, $second)
    {
        if ($first === $second) {
            return 0;
        }
        return (is_int($first) && is_int($second)) ? $first - $second : strcmp($first, $second);
    }

    /**
     * @param mixed $value
     * @return int|string
     */
    protected function sanitizeValue($value)
    {
        if (is_int($value) || ctype_digit($value)) {
            return (int) $value;
        } elseif (preg_match(self::REGEX_IDENTIFIER_ELEMENT, $value)) {
            return (string) $value;
        }
        throw SemverException::format('"%s" is not a valid version segment element', $value);
    }
}
