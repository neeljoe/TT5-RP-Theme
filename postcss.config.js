module.exports = ctx => ({
	map: ctx.env !== 'production',
	plugins: {
		'postcss-import': {},
		'postcss-preset-env': { stage: 3 },
		'cssnano': ctx.env === 'production' ? {} : false,
	},
});
