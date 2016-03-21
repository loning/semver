<?php

/*
 * Semver
 * (c) Omines Internetbureau B.V. - www.omines.nl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Tests;

use Omines\Semver\Expression;
use Omines\Semver\Version;

/**
 * ExpressionTest
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider variousRangesProvider
     * @param string $string
     */
    public function testExpressionParser($string)
    {
        $expression = Expression::fromString($string);
        $version = Version::fromString('6.8.4-alpha');

        $this->assertEquals($string, $expression->getOriginalString());
        $this->assertEquals($expression->matches($version), $version->matches($expression));
    }

    public function variousRangesProvider()
    {
        $expressions = [
            '^0.0.0',
            '^0.0.1',
            '^0.1.1',
            '~0.0.0',
            '~0.0.1',
            '~0.1.1',
            '=1.2.3',
            '>2.3.4',
            '<3.4.5',
            '!=4.5.6',
            '<>5.6.7',
            '>=4.5.6',
            '<=5.6.7',
            '~6.7.8',
            '^7.8.9',
            '1.2.3 - 2.3.4',
            '1.2 - 2.3',
            '1.2.x',
            '1.*',
            '2.X',
            '*',
            '1.2.3|2.3.4',
            ' 1.2.3 || 2.3.4',
            '1 - 2 | 4 - 5   ',
            '^1.2 | ~2.3 | ~3.4 | ^4.5',
            '^1.2 | ~2.3 | 3.4 - 3.5 | ^4.5',
        ];
        return array_combine($expressions, array_map(function ($item) { return [$item]; }, $expressions));
    }

    /**
     * @dataProvider expressionDataProvider
     *
     * @param string $string
     * @param string $type
     * @param Version $version
     */
    public function testExpression($string, $type, $version)
    {
        $expression = Expression::fromString($string);
        switch ($type) {
            case 'match':
                $this->assertTrue($expression->matches($version));
                break;
            case 'fail':
                $this->assertFalse($expression->matches($version));
                break;
            default:
                // TODO: Implement lt/gt
        }
    }

    public function expressionDataProvider()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/Data/Expressions/ExpressionMatches.json'), JSON_OBJECT_AS_ARRAY);
        foreach ($data as $expression => $tests) {
            foreach ($tests as $type => $versions) {
                foreach ($versions as $version) {
                    yield "$expression $type $version" => [$expression, $type, Version::fromString($version)];
                }
            }
        }
    }

    /**
     * @dataProvider normalizedExpressionDataProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testExpressionNormalization($input, $expected)
    {
        $this->assertEquals($expected, $expression = Expression::fromString($input));
        $this->assertEquals($expected, Expression::fromString($expression)->getNormalizedString());
    }

    public function normalizedExpressionDataProvider()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/Data/Expressions/NormalizedExpressions.json'), JSON_OBJECT_AS_ARRAY);
        foreach ($data as $key => $value) {
            yield $value => [$key, $value];
        }
    }
}
