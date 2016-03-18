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
 * VersionMap.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class VersionMap extends AbstractVersionCollection
{
    /**
     * VersionMap constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = array_combine(
            array_keys($data),
            array_map(function ($key, $value) {
                return [Version::fromString($key), $value];
            }, array_keys($data), array_values($data))
        );
    }

    /**
     * @param array $array
     * @return VersionMap
     */
    public static function fromArray(array $array)
    {
        return new self($array);
    }

    /**
     * @param callable $callable Callback that will be invoked with (&$data, $version) parameters.
     */
    public function each(callable $callable)
    {
        foreach ($this as $version => &$data) {
            $callable($data, $version);
        };
    }

    /**
     * @return Version[]
     */
    public function getKeys()
    {
        return $this->getVersionArray();
    }

    /**
     * @return string[]
     */
    public function getStringKeys()
    {
        return $this->getVersionStringArray();
    }

    /**
     * @return VersionMapIterator
     */
    public function getIterator()
    {
        return new VersionMapIterator($this->data);
    }

    /**
     * @return mixed[]
     */
    public function getValues()
    {
        return array_map(function ($item) {
            return $item[1];
        }, array_values($this->data));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->data[(string) $offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->data[(string) $offset][1];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $version = ($offset instanceof Version ? $offset : Version::fromString($offset));
        $this->data[(string) $offset] = [$version, $value];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[(string) $offset]);
    }
}
