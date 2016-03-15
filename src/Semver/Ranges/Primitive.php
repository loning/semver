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
        $this->version = $version instanceof Version ? $version : Version::fromString($version);
        $this->operator = $operator;
        $this->negate = (bool) $negate;
    }

    /**
     * @param string|Version $version
     * @param string $operator One of the simple or negated operators.
     * @return Primitive
     */
    public static function fromParts($version, $operator)
    {
        if (array_key_exists($operator, self::$inversions)) {
            return new self($version, $operator, false);
        } elseif (false !== ($inverted = array_search($operator, self::$inversions))) {
            return new self($version, $inverted, true);
        } elseif (self::OPERATOR_NE_ALT === $operator) {
            return new self($version, self::OPERATOR_EQ, true);
        }
        throw new SemverException(sprintf('Invalid primitive operator "%s%s"', $operator, $version));
    }

    /**
     * @return Primitive A primitive matching all versions.
     */
    public static function getWildcard()
    {
        return new self(Version::fromString('0'), Primitive::OPERATOR_LT, true);
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
                $result = !$comparison;
                break;
            case self::OPERATOR_GT:
                $result = ($comparison > 0);
                break;
            case self::OPERATOR_LT:
                $result = ($comparison < 0);
                break;
            default:
                throw new SemverException(sprintf('Invalid primitive operator "%s"', $this->operator));
        }
        return $this->negate xor $result;
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
