<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Version;

use Omines\Semver\Exception\SemverException;

/**
 * NumbersSegment.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class NumbersSegment extends AbstractSegment
{
    /**
     * @param int $index index to increment, all subsequent numbers will be reset
     */
    public function increment($index)
    {
        if (!isset($this[$index])) {
            throw SemverException::format('Index %d does not exist in numbers segment', $index);
        }
        ++$this->elements[$index];
        while (++$index < count($this->elements)) {
            $this->elements[$index] = 0;
        }
    }

    /**
     * @param string|mixed[]|null $segment
     * @return self
     */
    public function set($segment = null)
    {
        return parent::set($segment ?: [0, 0, 1]);
    }

    /**
     * @param int $first
     * @param int $second
     * @return int|float
     */
    protected function compareElements($first, $second)
    {
        return $first - $second;
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function sanitizeValue($value)
    {
        if (is_int($value) || ctype_digit($value)) {
            return (int) $value;
        }
        throw SemverException::format('"%s" is not a numeric version segment value', $value);
    }
}
