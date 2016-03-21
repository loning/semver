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
use Omines\Semver\Segments\AbstractSegment;
use Omines\Semver\Segments\NumbersSegment;

/**
 * VersionInterface
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
interface VersionInterface
{
    /**
     * @return AbstractSegment
     */
    function getPrerelease();

    /**
     * @return NumbersSegment
     */
    function getVersion();

    /**
     * @param ExpressionInterface $expression
     * @return bool
     */
    function matches(ExpressionInterface $expression);

    /**
     * @param VersionInterface $version
     * @return bool
     */
    function equals(VersionInterface $version);

    /**
     * @param VersionInterface $version
     * @return bool
     */
    function greaterThan(VersionInterface $version);

    /**
     * @param VersionInterface $version
     * @return bool
     */
    function lessThan(VersionInterface $version);
}
