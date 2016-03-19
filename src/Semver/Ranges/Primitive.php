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

    const OPERATOR_NE = '!=';
    const OPERATOR_GE = '>=';
    const OPERATOR_LE = '<=';

    const OPERATOR_NE_ALT = '<>';

    const REGEX_PRIMITIVE = '#^\s*(!=|<>|([><]?=?))([\d\.]+)\s*$#';

    private static $inversions = [
        self::OPERATOR_EQ => self::OPERATOR_NE,
        self::OPERATOR_GT => self::OPERATOR_LE,
        self::OPERATOR_LT => self::OPERATOR_GE,
    ];

    /** @var Version */
    private $version;

    /** @var string */
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
        if (!array_key_exists($operator, self::$inversions)) {
            throw SemverException::format('Invalid primitive operator "%s"', $operator);
        }

        $this->version = $version instanceof Version ? $version : Version::fromString($version);
        $this->operator = $operator;
        $this->negate = (bool) $negate;
    }

    /**
     * @return Primitive A primitive matching all versions.
     */
    public static function getWildcard()
    {
        return new self(Version::fromString('0'), self::OPERATOR_LT, true);
    }

    /**
     * @param Version|string $version
     * @return bool
     */
    public function satisfiedBy($version)
    {
        $version = $version instanceof Version ? $version : Version::fromString($version);
        $comparison = $version->compare($this->version);
        switch ($this->operator) {
            case self::OPERATOR_EQ:
                return $this->negate xor !$comparison;
            case self::OPERATOR_GT:
                return $this->negate xor ($comparison > 0);
            case self::OPERATOR_LT:
                return $this->negate xor ($comparison < 0);
        }
        // @codeCoverageIgnoreStart
        throw SemverException::format('Invalid primitive operator "%s"', $this->operator);
        // @codeCoverageIgnoreEnd
    }

    public function getNormalizedString()
    {
        $operator = $this->negate ? self::$inversions[$this->operator] : $this->operator;
        return ($operator === self::OPERATOR_EQ ? '' : $operator) . $this->version->getNormalizedString();
    }

    public function __toString()
    {
        return $this->getNormalizedString();
    }
}
