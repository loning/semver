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
 * AbstractVersionCollection.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class AbstractVersionCollection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /** @var array */
    protected $data;

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @return Version[] A standard PHP array of versions.
     */
    protected function getVersionArray()
    {
        return array_map(function($item) { return $item[0]; }, $this->data);
    }

    /**
     * @return string[] A standard PHP array of normalized versions.
     */
    protected function getVersionStringArray()
    {
        return array_map(function($item) { return (string)$item[0]; }, $this->data);
    }
}
