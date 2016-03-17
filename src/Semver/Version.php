<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver;

use Omines\Semver\Exception\SemverException;
use Omines\Semver\Parser\VersionParser;
use Omines\Semver\Ranges\Range;

/**
 * Semver Version number encapsulation.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Version
{
    const COMPLIANCE_NONE = 0;
    const COMPLIANCE_SEMVER2 = 1;
    const DEFAULT_SEMVER = '0.0.1';
    const INDEX_MAJOR = 0;
    const INDEX_MINOR = 1;
    const INDEX_PATCH = 2;

    /** @var int[] */
    private $version;

    /** @var mixed[] */
    private $prerelease;

    /** @var mixed[] */
    private $build;

    /** @var string */
    private $originalString;

    /** @var int */
    private $compliance;

    /**
     * Version constructor.
     *
     * @param string $version
     * @param int|bool $compliance Compliance level, or just false for loose
     */
    public function __construct($version = self::DEFAULT_SEMVER, $compliance = self::COMPLIANCE_SEMVER2)
    {
        $version = (string) $version;
        $this->originalString = $version;

        if (!($parsed = VersionParser::parse($version, $issues))) {
            throw end($issues);
        }
        if ($compliance != ($this->compliance = $parsed[VersionParser::COMPLIANCE]) && $compliance) {
            throw SemverException::format('Version "%s" is not of required compliance level', $version);
        }

        $this->version = $parsed[VersionParser::VERSION];
        $this->prerelease = $parsed[VersionParser::PRERELEASE];
        $this->build = $parsed[VersionParser::BUILD];
    }

    /**
     * @param string $version
     * @param int|bool $compliance Compliance level, or just false for loose
     * @return Version
     */
    public static function fromString($version, $compliance = self::COMPLIANCE_SEMVER2)
    {
        return new self($version, $compliance);
    }

    /**
     * Returns the greatest version of the supplied arguments or array.
     *
     * @param Version|Version[] $versions
     * @param Version ...
     * @return Version
     */
    public static function highest($versions)
    {
        return self::reduce(is_array($versions) ? $versions : func_get_args(), function (Version $carry, Version $version) {
            return $carry->compare($version) > 0;
        });
    }

    /**
     * Returns the lowest version of the supplied arguments or array.
     *
     * @param Version|Version[] $versions
     * @param Version ...
     * @return Version
     */
    public static function lowest($versions)
    {
        return self::reduce(is_array($versions) ? $versions : func_get_args(), function (Version $carry, Version $version) {
            return $carry->compare($version) < 0;
        });
    }

    /**
     * @param mixed[] $versions
     * @param callable $selector Returns true to select the first parameter, false for the second.
     * @return Version
     */
    private static function reduce(array $versions, callable $selector)
    {
        $versions = array_map(function ($element) {
            return $element instanceof Version ? $element : self::fromString($element);
        }, $versions);
        return array_reduce($versions, function (Version $carry, $version) use ($selector) {
            return $selector($carry, $version) ? $carry : $version;
        }, array_shift($versions));
    }

    /**
     * @param Version $that Version to compare to.
     * @return integer|double Negative is this is smaller, positive if that is smaller, or 0 if equals.
     */
    public function compare(Version $that)
    {
        return $this->compareByVersion($that) ?: $this->compareByPrerelease($that);
    }

    /**
     * @param Version $that
     * @return integer|double Negative is this is smaller, positive if that is smaller, or 0 if equals.
     */
    private function compareByVersion(Version $that)
    {
        $thoseNumbers = count($that->version);
        $theseNumbers = count($this->version);
        if ($thoseNumbers < $theseNumbers) {
            return -$that->compareByVersion($this);
        }
        for ($idx = 0; $idx < $theseNumbers; ++$idx) {
            if ($this->version[$idx] != $that->version[$idx]) {
                return $this->version[$idx] - $that->version[$idx];
            }
        }
        for (; $idx < $thoseNumbers; ++$idx) {
            if ($that->version[$idx]) {
                return -1;
            }
        }
        return 0;
    }

    /**
     * @param Version $that
     * @return int Negative is this is smaller, positive if that is smaller, or 0 if equals.
     */
    private function compareByPrerelease(Version $that)
    {
        if (!($leastPrereleases = min(count($this->prerelease), count($that->prerelease)))) {
            return count($that->prerelease) - count($this->prerelease);
        }
        for ($idx = 0; $idx < $leastPrereleases; ++$idx) {
            $left = &$this->prerelease[$idx];
            $right = &$that->prerelease[$idx];
            if ($left !== $right) {
                return is_int($left) ? $left - $right : strcmp($left, $right);
            }
        }
        return count($this->prerelease) - count($that->prerelease);
    }

    /**
     * @return Version A new version representing the next significant release (caret operator)
     */
    public function getNextSignificant()
    {
        $next = clone $this;
        $next->prerelease = [];
        $max = count($this->version);
        $index = 0;
        do {
            if ($this->version[$index]) {
                ++$next->version[$index++];
                while ($index < $max) {
                    $next->version[$index++] = 0;
                }
                return $next;
            }
        } while (++$index < $max);
        $next->version = [0,0,1];
        return $next;
    }

    /**
     * @param Version $that
     * @return bool
     */
    public function equals(Version $that)
    {
        return $this->compare($that) === 0;
    }

    /**
     * @param Version $that
     * @return bool
     */
    public function greaterThan(Version $that)
    {
        return $this->compare($that) > 0;
    }

    /**
     * @param Version $that
     * @return bool
     */
    public function greaterThanOrEqual(Version $that)
    {
        return $this->compare($that) >= 0;
    }

    /**
     * @param Version $that
     * @return bool
     */
    public function lessThan(Version $that)
    {
        return $this->compare($that) < 0;
    }

    /**
     * @param Version $that
     * @return bool
     */
    public function lessThanOrEqual(Version $that)
    {
        return $this->compare($that) <= 0;
    }

    /**
     * @param Range $range
     * @return bool
     */
    public function satisfies(Range $range)
    {
        return $range->satisfiedBy($this);
    }

    /**
     * @param int $index
     *
     * @return string|int|null
     */
    public function getBuildElement($index)
    {
        return isset($this->build[$index]) ? $this->build[$index] : null;
    }

    /**
     * @return string
     */
    public function getBuildString()
    {
        return implode('.', $this->build);
    }

    /**
     * @return string
     */
    public function getOriginalString()
    {
        return $this->originalString;
    }

    /**
     * @return int
     */
    public function getMajorVersion()
    {
        return $this->version[self::INDEX_MAJOR];
    }

    /**
     * @return int
     */
    public function getMinorVersion()
    {
        return $this->version[self::INDEX_MINOR];
    }

    /**
     * @return string
     */
    public function getNormalizedString()
    {
        $result = $this->getVersionNumber();
        if (!empty($this->prerelease)) {
            $result .= '-' . implode('.', $this->prerelease);
        }
        if (!empty($this->build)) {
            $result .= '+' . implode('.', $this->build);
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getPatchVersion()
    {
        return $this->version[self::INDEX_PATCH];
    }

    /**
     * @param int $index
     *
     * @return string|int|null
     */
    public function getPrereleaseElement($index)
    {
        return isset($this->prerelease[$index]) ? $this->prerelease[$index] : null;
    }

    /**
     * @return string
     */
    public function getPrereleaseString()
    {
        return implode('.', $this->prerelease);
    }

    /**
     * @return string
     */
    public function getVersionNumber()
    {
        return implode('.', $this->version);
    }

    /**
     * @return bool
     */
    public function isLooselyMatched()
    {
        return $this->compliance === self::COMPLIANCE_NONE;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getNormalizedString();
    }
}
