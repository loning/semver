<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Version;

use Omines\Semver\Expressions\ExpressionInterface;

/**
 * VersionInterface.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
interface VersionInterface
{
    /**
     * @return AbstractSegment
     */
    public function getPrerelease();

    /**
     * @return NumbersSegment
     */
    public function getVersion();

    /**
     * @param ExpressionInterface $expression
     * @return bool
     */
    public function matches(ExpressionInterface $expression);

    /**
     * @param VersionInterface $version
     * @return bool
     */
    public function equals(VersionInterface $version);

    /**
     * @param VersionInterface $version
     * @return bool
     */
    public function greaterThan(VersionInterface $version);

    /**
     * @param VersionInterface $version
     * @return bool
     */
    public function lessThan(VersionInterface $version);
}
