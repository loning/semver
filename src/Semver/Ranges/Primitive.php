<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
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

    private static $inversions = [
        self::OPERATOR_EQ => '!=',
        self::OPERATOR_GT => '<=',
        self::OPERATOR_LT => '>=',
    ];

    /** @var Version */
    private $version;

    /** @var int */
    private $operator;

    /** @var bool  */
    private $negate;

    /**
     * Primitive constructor.
     *
     * @param string|Version $version
     * @param string $operator
     * @param bool $negate
     */
    public function __construct($version, $operator, $negate = false)
    {
        $this->version = $version instanceof Version ? $version : Version::fromString($version);
        $this->operator = $operator;
        $this->negate = (bool) $negate;
    }

    /**
     * @param Version|string $version
     * @return bool
     */
    public function satisfiedBy($version)
    {
        $version = $version instanceof Version ? $version : Version::fromString($version);
        $comparison = $this->version->compare($version);
        switch ($this->operator) {
            case self::OPERATOR_EQ:
                return !!$comparison === $this->negate;
            case self::OPERATOR_GT:
                return $this->negate === ($comparison > 0);
            case self::OPERATOR_LT:
                return $this->negate === ($comparison < 0);
        }
        throw new SemverException(sprintf('Invalid primitive operator "%s"', $this->operator));
    }

    public function getNormalizedString()
    {
        $operator = $this->negate ? self::$inversions[$this->operator] : $this->operator;
        return ($operator === '=' ? '' : $operator) . $this->version->getNormalizedString();
    }

    public function __toString()
    {
        return $this->getNormalizedString();
    }
}
