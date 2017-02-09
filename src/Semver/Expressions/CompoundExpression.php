<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Expressions;

use Omines\Semver\Expression;
use Omines\Semver\Version\VersionInterface;

/**
 * Encapsulates a collection of primitives joined by a logical operator.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class CompoundExpression implements ExpressionInterface
{
    const DISJUNCTIVE = true;
    const CONJUNCTIVE = false;

    /** @var bool */
    private $type;

    /** @var ExpressionInterface[] */
    private $expressions = [];

    /**
     * CompoundExpression constructor.
     *
     * @param bool $type
     * @param array $expressions
     */
    public function __construct($type = self::DISJUNCTIVE, array $expressions = [])
    {
        $this->type = (bool) $type;
        $this->expressions = array_map(function ($expression) {
            return $expression instanceof ExpressionInterface ? $expression : Expression::fromString($expression);
        }, $expressions);
    }

    /**
     * @param ExpressionInterface $expression
     * @return self
     */
    public function add(ExpressionInterface $expression)
    {
        $this->expressions[] = $expression;
        return $this;
    }

    /**
     * @param ExpressionInterface[] $expressions
     * @return self
     */
    public function addMultiple(array $expressions)
    {
        foreach ($expressions as $expression) {
            $this->add($expression);
        }
        return $this;
    }

    /**
     * @param VersionInterface $version
     * @return bool
     */
    public function matches(VersionInterface $version)
    {
        foreach ($this->expressions as $expression) {
            if ($expression->matches($version) === $this->type) {
                return $this->type;
            }
        }
        return !$this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return implode($this->type ? ' || ' : ' ', $this->expressions);
    }
}
