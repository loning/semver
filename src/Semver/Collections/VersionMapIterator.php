<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Collections;

use Omines\Semver\Version;

/**
 * VersionMapIterator.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class VersionMapIterator extends AbstractVersionIterator
{
    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $item = current($this->data);
        return $item[1];
    }

    /**
     * @return Version
     */
    public function key()
    {
        $item = current($this->data);
        return $item[0];
    }
}
