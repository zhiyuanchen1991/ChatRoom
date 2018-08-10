var htmlWebpackPlugin = require('html-webpack-plugin');
var path = require('path');

module.exports = {
    mode: "development",
    entry: __dirname + "/Web/src/js/app.js",//已多次提及的唯一入口文件
    output: {
        path: __dirname + "/Web/dist",//打包后的文件存放的地方
        filename: "bundle.js"//打包后输出文件的文件名
    },
    devtool: 'eval-source-map',
    module: {
        rules: [
            {
                test: /(\.jsx|\.js)$/,
                use: {
                    loader: "babel-loader"
                },
                exclude: /node_modules/
            },
            {
                test: /\.css$/,
                use: [
                    {
                        loader: "style-loader"
                    },
                    {
                        loader: "css-loader"
                    }
                ]
            }
        ]
    },
    plugins: [
        new htmlWebpackPlugin({
            filename: './Web/index.php', //通过模板生成的文件名
            template: './Web/index.php',//模板路径
            inject: 'body' //是否自动在模板文件添加 自动生成的js文件链接
        })
    ]
};