<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);
$config = new PhpCsFixer\Config();
$config->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_align' => ['align' => 'left'],
        'simplified_null_return' => true,
        'phpdoc_no_package' => false,
        'concat_space' => ['spacing' => 'one'],
        'void_return' => true,
        'compact_nullable_typehint' => true,
        'combine_consecutive_issets' => true,
        'trailing_comma_in_multiline' => false,
    ])
    ->setFinder($finder);

return $config;
