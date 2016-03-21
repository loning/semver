<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver;

use Omines\Semver\Expressions\ExpressionInterface;
use Omines\Semver\Expressions\ExpressionParser;
use Omines\Semver\Expressions\Primitive;
use Omines\Semver\Parser\RangeParser;
use Omines\Semver\Version;
use Omines\Semver\Version\VersionInterface;

/**
 * Encapsulates a range of versions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Expression implements ExpressionInterface
{
    /** @var Primitive[][] */
    private $elements = [];

    /** @var string */
    private $originalString;

    /**
     * Range constructor.
     *
     * @param string $range
     */
    public function __construct($range)
    {
        $range = (string) $range;
        $this->originalString = $range;
        $this->elements = ExpressionParser::parseExpression($range);
    }

    /**
     * @param $range
     * @return Expression
     */
    public static function fromString($range)
    {
        return new self($range);
    }

    /**
     * @return string
     */
    public function getNormalizedString()
    {
        return implode(' || ', array_map(function ($and) {
            return implode(' ', $and);
        }, $this->elements));
    }

    /**
     * @return string
     */
    public function getOriginalString()
    {
        return $this->originalString;
    }

    /**
     * @param Version $version
     * @return bool Whether the version is within this range.
     */
    public function matches(VersionInterface $version)
    {
        foreach ($this->elements as $or) {
            foreach ($or as $and) {
                if (!$and->matches($version)) {
                    continue 2;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getNormalizedString();
    }
}
