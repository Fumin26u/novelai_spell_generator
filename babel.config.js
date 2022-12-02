module.exports = {
    presets: ['@vue/cli-plugin-babel/preset'],
    env: {
        test: {
            presets: ['@babel/preset-env'],
            plugins: [
                ['@babel/plugin-proposal-decorators', { legacy: true }],
                '@babel/plugin-proposal-function-sent',
                '@babel/plugin-proposal-export-namespace-from',
                '@babel/plugin-proposal-numeric-separator',
                '@babel/plugin-proposal-throw-expressions',
            ],
        },
        production: {
            plugins: ['transform-remove-console'],
        },
    },
}
