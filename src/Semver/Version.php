<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver;

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
        $this->originalString = $version;

        if ($compliance != self::COMPLIANCE_SEMVER2) {
            throw new \InvalidArgumentException('Only Semver2 parsing is supported right now');
        }

        $parsed = Parser::parseSemver2($version);
        $this->version = $parsed[Parser::SECTION_VERSION];
        $this->prerelease = $parsed[Parser::SECTION_PRERELEASE];
        $this->build = $parsed[Parser::SECTION_BUILD];
        $this->compliance = self::COMPLIANCE_SEMVER2;
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
     * Returns the greatest version of the supplied arguments.
     *
     * @param Version $version
     * @param Version ...
     * @return Version
     */
    public static function greatest(Version $version)
    {
        $result = $version;
        /** @var Version $version */
        foreach (array_slice(func_get_args(), 1) as $version) {
            if ($version->compare($result) > 0) {
                $result = $version;
            }
        }
        return $result;
    }

    /**
     * @param Version $that Version to compare to.
     * @return int Negative is this is smaller, positive if that is smaller, or 0 if equals.
     */
    public function compare(Version $that)
    {
        return $this->compareByVersion($that) ?: $this->compareByPrerelease($that);
    }

    /**
     * @param Version $that
     * @return int Negative is this is smaller, positive if that is smaller, or 0 if equals.
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
                return 1;
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
            if ($this->prerelease[$idx] !== $that->prerelease[$idx]) {
                return strcmp($this->prerelease[$idx], $that->prerelease[$idx]);
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
    public function equals(Version $that)
    {
        return $this->compare($that) === 0;
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
