<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver;

use Omines\Semver\Expressions\CompoundExpression;
use Omines\Semver\Expressions\ExpressionInterface;
use Omines\Semver\Expressions\ExpressionParser;
use Omines\Semver\Expressions\Primitive;
use Omines\Semver\Version;
use Omines\Semver\Version\VersionInterface;

/**
 * Encapsulates an expression covering a range of versions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Expression implements ExpressionInterface
{
    /** @var string */
    private $originalString;

    /** @var CompoundExpression */
    private $rootExpression;

    /**
     * Range constructor.
     *
     * @param string $expression
     */
    public function __construct($expression)
    {
        $expression = (string) $expression;
        $this->originalString = $expression;
        $this->rootExpression = ExpressionParser::parseExpression($expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function fromString($expression)
    {
        return new self($expression);
    }

    /**
     * @return string
     */
    public function getNormalizedString()
    {
        return (string) $this->rootExpression;
    }

    /**
     * @return string
     */
    public function getOriginalString()
    {
        return $this->originalString;
    }

    /**
     * @param VersionInterface $version
     * @return bool Whether the version is matches by this expression.
     */
    public function matches(VersionInterface $version)
    {
        return $this->rootExpression->matches($version);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getNormalizedString();
    }
}
