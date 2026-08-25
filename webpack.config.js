const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

const config = Array.isArray(defaultConfig) ? defaultConfig[0] : defaultConfig;

config.entry = {
    editor: path.resolve(__dirname, 'assets/src/editor.js')
};

config.output = {
    ...(config.output || {}),
    path: path.resolve(__dirname, 'assets/build'),
    filename: '[name].js',
};

module.exports = config;