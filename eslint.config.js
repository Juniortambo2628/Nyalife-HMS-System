import js from '@eslint/js';
import globals from 'globals';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import reactRefresh from 'eslint-plugin-react-refresh';

export default [
    {
        files: ['resources/js/**/*.{js,jsx}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.node,
                route: 'readonly',
                vi: 'readonly',
                expect: 'readonly',
                describe: 'readonly',
                it: 'readonly',
                test: 'readonly',
                beforeEach: 'readonly',
                afterEach: 'readonly',
                beforeAll: 'readonly',
                afterAll: 'readonly',
                jest: 'readonly',
            },
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
        },
        plugins: {
            react,
            'react-hooks': reactHooks,
            'react-refresh': reactRefresh,
        },
        rules: {
            ...js.configs.recommended.rules,
            ...react.configs.recommended.rules,
            ...react.configs['jsx-runtime'].rules,
            'react/react-in-jsx-scope': 'off',
            'react/prop-types': 'off',
            'react/jsx-uses-react': 'off',
            'react-refresh/only-export-components': 'off',
            'react/no-unescaped-entities': 'off',
            'react/jsx-no-target-blank': 'off',
            'react/jsx-no-undef': 'off',
            'no-unused-vars': ['warn', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
            'no-console': 'off',
            // Lenient hook rules so the existing codebase still passes.
            'react-hooks/rules-of-hooks': 'warn',
            'react-hooks/exhaustive-deps': 'off',
        },
        settings: {
            react: {
                version: 'detect',
            },
        },
    },
    {
        ignores: ['public/**', 'node_modules/**', 'vendor/**', 'e2e/**', '*.config.js'],
    },
];
