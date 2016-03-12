<?php

$header = <<<'EOF'
Semver
(c) Omines Internetbureau B.V. - www.omines.nl

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->finder(
        Symfony\CS\Finder::create()
            ->files()
            ->name('*.php')
            ->exclude('Data')
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    )
;