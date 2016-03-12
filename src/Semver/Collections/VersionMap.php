<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
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
    public function getKeys()
    {
        return $this->getVersionArray();
    }

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
        return array_map(function ($item) { return $item[1]; }, array_values($this->data));
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
