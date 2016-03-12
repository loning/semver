<?php
/**
 * Semver
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\Semver\Ranges;

/**
 * Encapsulates a range of versions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Range
{
    public function __construct($range)
    {
        $elements = preg_split('/\s*\|{1,2}\s*/', trim($range));
        foreach ($elements as $element) {
            $subs = preg_split('/\s+/', $element);
        }
    }

    public static function fromString($range)
    {
        return new self($range);
    }
}
