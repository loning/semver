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
class Segment implements \ArrayAccess, \Countable
{
    const REGEX_SEGMENT_ELEMENT = '#^[A-Za-z0-9\-]+$#';

    /**
     * @var mixed[]
     */
    private $elements = [];
    
    /**
     * Segment constructor.
     * @param string|mixed[] $segment
     */
    public function __construct($segment = [])
    {
        if ($segment) {
            $this->elements = array_map([$this, 'sanitizeValue'], is_array($segment) ? $segment : explode('.', $segment));
        }
    }

    /**
     * @param Segment $that
     * @return integer|double Negative is this is smaller, positive if that is smaller, or 0 if equals.
     */
    public function compare(Segment $that)
    {
        if (!($leastPrereleases = min(count($this->elements), count($that->elements)))) {
            return count($that->elements) - count($this->elements);
        }
        for ($idx = 0; $idx < $leastPrereleases; ++$idx) {
            $left = &$this->elements[$idx];
            $right = &$that->elements[$idx];
            if ($left !== $right) {
                return is_int($left) ? $left - $right : strcmp($left, $right);
            }
        }
        return count($this->elements) - count($that->elements);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return ($offset >= 0 && $offset < count($this->elements)) ? $this->elements[$offset] : null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $value = $this->sanitizeValue($value);
        if (is_null($offset)) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        array_splice($this->elements, $offset, 1);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode('.', $this->elements);
    }

    /**
     * @param mixed $value
     * @return int|string
     */
    private function sanitizeValue($value)
    {
        if (ctype_digit($value)) {
            return (int) $value;
        } elseif (preg_match(self::REGEX_SEGMENT_ELEMENT, $value)) {
            return (string) $value;
        }
        throw SemverException::format('"%s" is not a valid version segment element', $value);
    }
}
