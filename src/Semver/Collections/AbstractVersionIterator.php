<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Collections;

/**
 * AbstractVersionIterator.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class AbstractVersionIterator implements \Iterator
{
    /** @var array */
    protected $data;

    /**
     * AbstractVersionIterator constructor.
     *
     * @param array $data Data to loop.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return null !== key($this->data);
    }
}
