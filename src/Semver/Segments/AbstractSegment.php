<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Segments;

/**
 * AbstractSegment
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class AbstractSegment implements \ArrayAccess, \Countable
{
    /**
     * @var mixed[]
     */
    protected $elements = [];

    /**
     * Segment constructor.
     * @param string|mixed[] $segment
     */
    public function __construct($segment = [])
    {
        if ($segment) {
            $this->elements = array_map(function ($value) {
                return $this->sanitizeValue($value);
            }, is_array($segment) ? $segment : explode('.', $segment));
        }
    }

    /**
     * @param AbstractSegment $that
     * @return integer|double Negative is this is smaller, positive if that is smaller, or 0 if equals.
     */
    public function compare(AbstractSegment $that)
    {
        $thoseElements = count($that->elements);
        $theseElements = count($this->elements);
        if (!$thoseElements || !$theseElements) {
            return count($that->elements) - count($this->elements);
        } elseif($thoseElements > $theseElements) {
            return -$that->compare($this);
        }
        for ($idx = 0; $idx < $theseElements; ++$idx) {
            if ($result = $this->compareElements($this[$idx], $that[$idx])) {
                return $result;
            }
        }
        return 0;
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
     * @param mixed $first
     * @param mixed $second
     * @return int|double
     */
    abstract protected function compareElements($first, $second);

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract protected function sanitizeValue($value);
}
