<?php

declare(strict_types=1);

/**
 * PHP CS Fixer Configuration for HD Tickets
 * 
 * This configuration ensures full PSR-12 compliance with additional
 * Laravel-specific rules and modern PHP practices.
 * 
 * @package HDTickets
 * @author  Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle
 */

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->exclude([
        'vendor',
        'node_modules',
        'storage',
    ]);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        // PSR-12 Base ruleset
        '@PSR12' => true,
        
        // PHP 8.x specific rules
        '@PHP84Migration' => true,
        
        // Array notation
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'normalize_index_brace' => true,
        'whitespace_after_comma_in_array' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters', 'match'],
        ],
        
        // Binary operators
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '=' => 'single_space',
            ],
        ],
        'concat_space' => ['spacing' => 'one'],
        'operator_linebreak' => ['only_booleans' => true],
        
        // Blank lines
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try', 'if', 'foreach', 'for', 'while'],
        ],
        
        // Casing
        'constant_case' => ['case' => 'upper'],
        'lowercase_keywords' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,
        
        // Cast notation
        'cast_spaces' => ['space' => 'single'],
        'lowercase_cast' => true,
        'no_short_bool_cast' => true,
        'short_scalar_cast' => true,
        
        // Class notation
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'class_definition' => [
            'multi_line_extends_each_single_line' => true,
            'single_item_single_line' => true,
            'single_line' => true,
        ],
        'no_blank_lines_after_class_opening' => true,
        'no_null_property_initialization' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'method_public',
                'method_protected',
                'method_private',
                'magic',
            ],
        ],
        'protected_to_private' => true,
        'self_accessor' => true,
        'single_class_element_per_statement' => ['elements' => ['const', 'property']],
        'visibility_required' => ['elements' => ['method', 'property']],
        
        // Comments
        'comment_to_phpdoc' => [
            'ignored_tags' => ['todo', 'fixme'],
        ],
        'multiline_comment_opening_closing' => true,
        'no_empty_comment' => true,
        'no_trailing_whitespace_in_comment' => true,
        'single_line_comment_style' => ['comment_types' => ['asterisk', 'hash']],
        
        // Control structure
        'elseif' => true,
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'no_unneeded_control_parentheses' => true,
        'no_useless_else' => true,
        'simplified_if_return' => true,
        'switch_continue_to_break' => true,
        'yoda_style' => false,
        
        // Function notation
        'function_declaration' => ['closure_function_spacing' => 'one'],
        'implode_call' => true,
        'lambda_not_used_import' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ],
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
        ],
        'no_spaces_after_function_name' => true,
        'no_unreachable_default_argument_value' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'single_line_throw' => false,
        'void_return' => true,
        
        // Import
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'no_leading_import_slash' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        
        // Language construct
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_equal_normalize' => ['space' => 'none'],
        'dir_constant' => true,
        'is_null' => true,
        'modernize_types_casting' => true,
        'no_unset_on_property' => true,
        
        // List notation
        'list_syntax' => ['syntax' => 'short'],
        
        // Namespace
        'blank_line_after_namespace' => true,
        'clean_namespace' => true,
        'no_leading_namespace_whitespace' => true,
        
        // Naming
        'no_homoglyph_names' => true,
        
        // Operator
        'new_with_parentheses' => true,
        'object_operator_without_whitespace' => true,
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'unary_operator_spaces' => true,
        
        // PHP tag
        'blank_line_after_opening_tag' => false,
        'echo_tag_syntax' => ['format' => 'short'],
        'full_opening_tag' => true,
        'no_closing_tag' => true,
        
        // PHPDoc
        'align_multiline_comment' => ['comment_type' => 'phpdocs_only'],
        'general_phpdoc_annotation_remove' => [
            'annotations' => ['author', 'package', 'subpackage'],
        ],
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => false,
        ],
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_line_span' => [
            'const' => 'single',
            'method' => 'multi',
            'property' => 'single',
        ],
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => [
            'order' => [
                'param',
                'throws',
                'return',
            ],
        ],
        'phpdoc_order_by_value' => ['annotations' => ['covers']],
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => false,
        'phpdoc_tag_type' => ['tags' => ['inheritdoc' => 'inline']],
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,
        
        // Return notation
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => true,
        
        // Semicolon
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],
        
        // String notation
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'simple_to_complex_string_variable' => true,
        'single_quote' => ['strings_containing_single_quote_chars' => false],
        
        // Strict
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        
        // Whitespace
        'array_indentation' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'continue',
                'declare',
                'return',
                'throw',
                'try',
            ],
        ],
        'compact_nullable_type_declaration' => true,
        'heredoc_indentation' => ['indentation' => 'start_plus_one'],
        'indentation_type' => true,
        'line_ending' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'no_spaces_around_offset' => ['positions' => ['inside', 'outside']],
        'spaces_inside_parentheses' => ['space' => 'none'],
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        
        // Laravel specific
        'not_operator_with_successor_space' => true,
    ])
    ->setFinder($finder)
    ->setLineEnding("\n")
    ->setIndent('    ') // 4 spaces as per PSR-12
    ->setRiskyAllowed(true);
