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

        // Split disjunctive elements
        $elements = preg_split('/\s*\|{1,2}\s*/', trim($range));
        foreach ($elements as $element) {
            // Detect hyphen
            if (preg_match('/^\s*([^\s]+)\s+\-\s+([^\s]+)\s*$/', $element, $parts)) {
                $primitives = [
                    new Primitive(Version::fromString($parts[1]), Primitive::OPERATOR_LT, true),
                    new Primitive(Version::fromString($parts[2]), Primitive::OPERATOR_GT, true),
                ];
            } else {
                $primitives = [];
                foreach (preg_split('/\s+/', $element) as $simple) {
                    if (!preg_match('/^(\^|~|([><]?=?))(.*)$/', $simple, $parts)) {
                        throw new SemverException(sprintf('Could not parse simple constraint "%s"', $simple));
                    }
                    $version = Version::fromString($parts[3] ?: '*');
                    switch ($parts[1] ?: '=') {
                        case '>':
                            $primitives[] = new Primitive($version, Primitive::OPERATOR_GT);
                            break;
                        case '<':
                            $primitives[] = new Primitive($version, Primitive::OPERATOR_LT);
                            break;
                        case '>=':
                            $primitives[] = new Primitive($version, Primitive::OPERATOR_LT, true);
                            break;
                        case '<=':
                            $primitives[] = new Primitive($version, Primitive::OPERATOR_GT, true);
                            break;
                        case '=':
                            $primitives[] = new Primitive($version, Primitive::OPERATOR_EQ);
                            break;
                        case '!=':
                        case '<>':
                            $primitives[] = new Primitive($version, Primitive::OPERATOR_EQ, true);
                            break;
                        case '^':
                            $primitives[] = new Primitive($version, Primitive::OPERATOR_LT, true);
                            $primitives[] = new Primitive($version->getNextSignificant(), Primitive::OPERATOR_LT);
                            break;
                        case '~':
                            break;
                        default:
                            throw new \RuntimeException($parts[1]);
                    }
                }
            }
            if (empty($primitives)) {
                throw new SemverException('Range part cannot be empty');
            }
            $this->elements[] = $primitives;
        }
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
