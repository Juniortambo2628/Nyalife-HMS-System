module.exports = {
  env: {
    browser: true,
    es2021: true,
    jquery: true
  },
  extends: 'eslint:recommended',
  parserOptions: {
    ecmaVersion: 12,
    sourceType: 'module'
  },
  globals: {
    Components: 'readonly',
    FullCalendar: 'readonly',
    $: 'readonly'
  },
  rules: {
    'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
    'no-console': 'warn'
  }
};

