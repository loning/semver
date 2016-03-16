<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Ranges;

use Omines\Semver\Parser\RangeParser;
use Omines\Semver\Version;

/**
 * Encapsulates a range of versions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Range
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
        $this->originalString = $range;
        $this->elements = RangeParser::parseRangeSet($range);
    }

    /**
     * @param $range
     * @return Range
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
    public function satisfiedBy(Version $version)
    {
        foreach ($this->elements as $or) {
            foreach ($or as $and) {
                if (!$and->satisfiedBy($version)) {
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
