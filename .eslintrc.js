module.exports = {
    root: true,
    env: {
        node: true,
        'vue/setup-compiler-macros': true,
    },
    plugins: ['prettier'],
    extends: [
        'plugin:vue/vue3-essential',
        'eslint:recommended',
        '@vue/typescript',
    ],
    parserOptions: {
        parser: '@typescript-eslint/parser',
    },
    rules: {
        'no-unused-vars': 'off',
        'vue/no-unused-vars': 'off',
        'vue/script-setup-uses-vars': 'off',
        '@typescript-eslint/no-unused-vars': ['off'],
    },
}
