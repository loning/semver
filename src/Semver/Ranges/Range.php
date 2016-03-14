<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Ranges;

use Omines\Semver\Parser;
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
        $this->elements = Parser::parseRangeSet($range);
    }

    public static function fromString($range)
    {
        return new self($range);
    }

    /**
     * @return string
     */
    public function getNormalizedString()
    {
        return implode(' || ', array_map(function ($and) { return implode(' ', $and); }, $this->elements));
    }

    public function getOriginalString()
    {
        return $this->originalString;
    }

    public function matches(Version $version)
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

    public function __toString()
    {
        return $this->getNormalizedString();
    }
}
