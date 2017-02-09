<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Expressions;

use Omines\Semver\Version\VersionInterface;

/**
 * Interface ExpressionInterface.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
interface ExpressionInterface
{
    /**
     * @param VersionInterface $version
     * @return bool
     */
    public function matches(VersionInterface $version);
}
