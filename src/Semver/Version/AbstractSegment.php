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
 * AbstractSegment.
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
     * @param string|mixed[]|null $segment
     */
    public function __construct($segment = null)
    {
        $this->set($segment);
    }

    /**
     * @param AbstractSegment $that
     * @return int|float negative if this is smaller, positive if that is smaller, or 0 if equals
     */
    public function compare(AbstractSegment $that)
    {
        $thoseElements = count($that->elements);
        $theseElements = count($this->elements);
        if (!$thoseElements || !$theseElements) {
            return count($that->elements) - count($this->elements);
        } elseif ($thoseElements > $theseElements) {
            return -$that->compare($this);
        }
        foreach ($this->elements as $key => $value) {
            if ($result = $this->compareElements($value, $that[$key])) {
                return $result;
            }
        }
        return 0;
    }

    /**
     * @param string|mixed[]|null $segment
     * @return self
     */
    public function set($segment = null)
    {
        if ($segment) {
            $this->elements = array_map(function ($value) {
                return $this->sanitizeValue($value);
            }, is_array($segment) ? $segment : explode('.', $segment));
        } else {
            $this->elements = [];
        }
        return $this;
    }

    /**
     * @return self
     */
    public function reset()
    {
        return $this->set();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return ($offset >= 0 && $offset < count($this->elements)) ? $this->elements[$offset] : null;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * @return int|float
     */
    abstract protected function compareElements($first, $second);

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract protected function sanitizeValue($value);
}
