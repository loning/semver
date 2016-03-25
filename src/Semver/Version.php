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
use Omines\Semver\Expressions\ExpressionInterface;
use Omines\Semver\Version\IdentifierSegment;
use Omines\Semver\Version\NumbersSegment;
use Omines\Semver\Version\PrereleaseSegment;
use Omines\Semver\Version\VersionInterface;
use Omines\Semver\Version\VersionParser;

/**
 * Semver Version number encapsulation.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Version implements VersionInterface
{
    const COMPLIANCE_NONE = 0;
    const COMPLIANCE_SEMVER2 = 1;
    const DEFAULT_SEMVER = '0.0.1';
    const INDEX_MAJOR = 0;
    const INDEX_MINOR = 1;
    const INDEX_PATCH = 2;

    /** @var NumbersSegment */
    private $version;

    /** @var PrereleaseSegment */
    private $prerelease;

    /** @var IdentifierSegment */
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
     * @throws SemverException in case of any parsing failures
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

        $this->version = new NumbersSegment($parsed[VersionParser::VERSION]);
        $this->prerelease = new PrereleaseSegment($parsed[VersionParser::PRERELEASE]);
        $this->build = new IdentifierSegment($parsed[VersionParser::BUILD]);
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
     * @param VersionInterface $that Version to compare to.
     * @return integer|double Negative is this is smaller, positive if that is smaller, or 0 if equals.
     */
    public function compare(VersionInterface $that)
    {
        return $this->version->compare($that->getVersion()) ?:
               $this->prerelease->compare($that->getPrerelease());
    }

    /**
     * @return Version A new version representing the next significant release (caret operator)
     */
    public function getNextSignificant()
    {
        $next = clone $this;
        $next->prerelease->reset();
        $max = count($this->version);
        $index = 0;
        do {
            if ($this->version[$index]) {
                $next->version[$index++] += 1;
                while ($index < $max) {
                    $next->version[$index++] = 0;
                }
                return $next;
            }
        } while (++$index < $max);
        $next->version = new NumbersSegment([0, 0, 1]);
        return $next;
    }

    /**
     * Increases the given version number part, resetting those after to 0.
     *
     * @param int $index
     * @param string[]|string $prerelease
     * @param string[]|string $build
     * @return self
     */
    public function increment($index, $prerelease = [], $build = [])
    {
        $this->version->increment($index);
        $this->setPrerelease($prerelease);
        $this->setBuild($build);
        return $this;
    }

    /**
     * @param VersionInterface $that
     * @return bool
     */
    public function equals(VersionInterface $that)
    {
        return $this->compare($that) === 0;
    }

    /**
     * @param VersionInterface $that
     * @return bool
     */
    public function greaterThan(VersionInterface $that)
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
     * @param VersionInterface $that
     * @return bool
     */
    public function lessThan(VersionInterface $that)
    {
        return $this->compare($that) < 0;
    }

    /**
     * @param VersionInterface $that
     * @return bool
     */
    public function lessThanOrEqual(VersionInterface $that)
    {
        return $this->compare($that) <= 0;
    }

    /**
     * @param ExpressionInterface $expression
     * @return bool
     */
    public function matches(ExpressionInterface $expression)
    {
        return $expression->matches($this);
    }

    /**
     * @param int $index
     *
     * @return string|int|null
     */
    public function getBuildElement($index)
    {
        return $this->build[$index];
    }

    /**
     * @return IdentifierSegment
     */
    public function getBuild()
    {
        return $this->build;
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
    public function getMajor()
    {
        return $this->version[self::INDEX_MAJOR];
    }

    /**
     * @return int
     */
    public function getMinor()
    {
        return $this->version[self::INDEX_MINOR];
    }

    /**
     * @return string
     */
    public function getNormalizedString()
    {
        $result = (string) $this->getVersion();
        if (count($this->prerelease)) {
            $result .= '-' . $this->getPrerelease();
        }
        if (count($this->build)) {
            $result .= '+' . $this->getBuild();
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getPatch()
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
        return $this->prerelease[$index];
    }

    /**
     * @return IdentifierSegment
     */
    public function getPrerelease()
    {
        return $this->prerelease;
    }

    /**
     * @return NumbersSegment
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return bool
     */
    public function isLooselyMatched()
    {
        return $this->compliance === self::COMPLIANCE_NONE;
    }

    /**
     * @param string[]|string|null $build
     * @return $this
     */
    public function setBuild($build = [])
    {
        $this->build->set($build);
        return $this;
    }

    /**
     * @param string[]|string|null $prerelease
     * @return $this
     */
    public function setPrerelease($prerelease = null)
    {
        $this->prerelease->set($prerelease);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->version = clone $this->version;
        $this->prerelease = clone $this->prerelease;
        $this->build = clone $this->build;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getNormalizedString();
    }
}
