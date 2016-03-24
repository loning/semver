<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Collections;

use Omines\Semver\Version;

/**
 * VersionList.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class VersionList extends AbstractVersionCollection
{
    /**
     * VersionList constructor.
     *
     * @param string[] $data
     */
    public function __construct(array $data = [])
    {
        $this->data = array_map(function ($element) {
            return [Version::fromString($element)];
        }, $data);
    }

    /**
     * @param string[] $array
     * @return VersionList
     */
    public static function fromArray(array $array)
    {
        return new self($array);
    }

    /**
     * @param callable $callable
     */
    public function each(callable $callable)
    {
        foreach ($this as $version) {
            $callable($version);
        };
    }

    /**
     * @return VersionListIterator
     */
    public function getIterator()
    {
        return new VersionListIterator($this->data);
    }

    /**
     * @return string[] A standard PHP array of normalized versions.
     */
    public function getStringValues()
    {
        return $this->getVersionStringArray();
    }

    /**
     * @return Version[] A standard PHP array of versions.
     */
    public function getValues()
    {
        return $this->getVersionArray();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset][0];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = [Version::fromString($value)];
        } else {
            $this->data[$offset] = [Version::fromString($value)];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        array_splice($this->data, $offset, 1);
    }

    /**
     * Sorts the list to be descending according to Semver rules.
     */
    public function rsort()
    {
        usort($this->data, function ($first, $second) {
            /* @var Version[] $first */
            return -($first[0]->compare($second[0]));
        });
    }

    /**
     * Sorts the list to be ascending according to Semver rules.
     */
    public function sort()
    {
        usort($this->data, function ($first, $second) {
            /* @var Version[] $first */
            return $first[0]->compare($second[0]);
        });
    }
}
