<?php

$finder = \PhpCsFixer\Finder::create()
    ->exclude('Resources')
    ->exclude('Documentation')
    ->in(__DIR__);

$config = new \PhpCsFixer\Config();

return $config
    ->setCacheFile('.Build/.php-cs-fixer-risky.cache')
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'declare_strict_types' => true,
    ])
    ->setLineEnding("\n");
