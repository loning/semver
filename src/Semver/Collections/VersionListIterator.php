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
 * VersionListIterator.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class VersionListIterator extends AbstractVersionIterator
{
    /**
     * @return Version
     */
    public function current()
    {
        $item = current($this->data);
        return $item[0];
    }
}
