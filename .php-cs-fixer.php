<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['bootstrap', 'storage', 'vendor', 'database/migrations'])
    ->notName(['_*.php'])
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules([
        //TODO: check in the future if this issue will be closed: https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4955
        //TODO: while It doesn't have support to PSR12, we can use this recommendation: https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4502#issuecomment-570408101
        //TODO: for configuration -> https://mlocati.github.io/php-cs-fixer-configurator/#version:2.16
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => ['statements' => []],
        'control_structure_braces' => true,
        'compact_nullable_typehint' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => ['space' => 'none'],
        'declare_strict_types' => true,
        'type_declaration_spaces' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'new_with_braces' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return',
                'square_brace_block', 'switch', 'throw', 'use',
            ]
        ],
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'return_assignment' => false,
        'return_type_declaration' => ['space_before' => 'none'],
        'single_import_per_statement' => false,
        'single_trait_insert_per_statement' => false,
        'trailing_comma_in_multiline' => false,
        'no_superfluous_phpdoc_tags' => false,
        'yoda_style' => ['always_move_variable' => false, 'equal' => true, 'identical' => true, 'less_and_greater' => null],
    ])->setFinder($finder);
