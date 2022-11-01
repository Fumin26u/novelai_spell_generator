const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
    publicPath: './',
    transpileDependencies: true, 
    devServer: {
        // proxy: {
        //     "^/api*": {
        //         target: "http://localhost/",
        //         pathRewrite: {"^/api": ""},
        //         changeOrigin: true,
        //         logLevel: "debug",
        //     }
        // },
    },
})
