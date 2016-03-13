<?php

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader(<<<EOF
Semver
(c) Omines Internetbureau B.V. - www.omines.nl

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF
);

return Symfony\CS\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        'newline_after_open_tag',
        'single_blank_line_before_namespace',
        '-return',
        'concat_with_spaces',
        'short_array_syntax',
        'strict_param',
        'header_comment'
    ])
    ->finder(
        Symfony\CS\Finder::create()
            ->files()
            ->name('*.php')
            ->exclude('Data')
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    )
;