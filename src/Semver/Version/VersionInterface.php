<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Version;

use Omines\Semver\Expressions\ExpressionInterface;

/**
 * VersionInterface
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
interface VersionInterface
{
    function matches(ExpressionInterface $expression);
}
