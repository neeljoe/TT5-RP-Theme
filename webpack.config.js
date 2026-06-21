const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
	...defaultConfig,
	entry: {
		interactivity: './resources/js/interactivity.js',
	},
	experiments: {
		outputModule: true,
	},
	output: {
		path: __dirname + '/public/js',
		filename: '[name].js',
		module: true,
	},
};
