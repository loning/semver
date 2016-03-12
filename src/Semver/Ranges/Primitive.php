<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Ranges;

use Omines\Semver\Exception\SemverException;
use Omines\Semver\Version;

/**
 * Primitive.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Primitive
{
    const OPERATOR_EQ = '=';
    const OPERATOR_GT = '>';
    const OPERATOR_LT = '<';

    /** @var Version */
    private $version;

    /** @var int */
    private $operator;

    /** @var bool  */
    private $negate;

    public function __construct(Version $version, $operator, $negate = false)
    {
        $this->version = $version;
        $this->operator = $operator;
        $this->negate = (bool) $negate;
    }

    public function satisfy(Version $version)
    {
        $comparison = $this->version->compare($version);
        switch ($this->operator) {
            case self::OPERATOR_EQ:
                return !$comparison === $this->negate;
            case self::OPERATOR_GT:
                return $this->negate === ($comparison > 0);
            case self::OPERATOR_LT:
                return $this->negate === ($comparison < 0);
        }
        throw new SemverException(sprintf('Invalid primitive operator %d', $this->operator));
    }
}
