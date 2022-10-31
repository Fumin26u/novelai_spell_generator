const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
    publicPath: './',
    transpileDependencies: true, 
    devServer: {
        proxy: {
            "https://google.co.jp": {
                target: "http://localhost:8081",
            }
        },
        host: 'localhost',
        port: 8081,
    },
})
