var path = require('path')
var webpack = require('webpack')
var HtmlWebpackPlugin = require('html-webpack-plugin');

module.exports = {
	entry: { 
		main: './src/main.js' 
	},
	output: {
		path: path.resolve(__dirname, './dist'),
		publicPath: 'app/',
		filename: '[name].[chunkhash].js'
	},
	optimization: {
		splitChunks: { 
			cacheGroups: {
				vendor: {
					test: /[\\/]node_modules[\\/]/,
					name: 'vendor',
					chunks: 'all',
				},
				default: false
			}
		},
		noEmitOnErrors: true, 
	},
	plugins : [
		new HtmlWebpackPlugin({
			filename : "index.php",
			template : "index.template.php"
		}),
		new webpack.HashedModuleIdsPlugin(),
	],
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				options: {
					loaders: {
						// Since sass-loader (weirdly) has SCSS as its default parse mode, we map
						// the "scss" and "sass" values for the lang attribute to the right configs here.
						// other preprocessors should work out of the box, no loader config like this necessary.
						'scss': 'vue-style-loader!css-loader!sass-loader',
						'sass': 'vue-style-loader!css-loader!sass-loader?indentedSyntax'
					}
					// other vue-loader options go here
				}
			},
			{
				test: /\.(s?css)$/,
				use : [{
					loader: "style-loader" // creates style nodes from JS strings
				}, {
					loader: "css-loader" // translates CSS into CommonJS
				}, {
					loader: "sass-loader" // compiles Sass to CSS
				}]
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/
			},
			{
				test: /\.(png|jpg|gif|svg|otf|woff|woff2|ttf|eot)$/,
				loader: 'file-loader',
				options: {
					name: '[name].[ext]?[hash]'
				}
			}
		]
	},
	resolve: {
		alias: {
			'vue$': 'vue/dist/vue.runtime.esm.js',
		}
	},
	devtool : 'cheap-source-map'
}
