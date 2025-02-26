const glob = require('glob');

// Keenthemes' plugins
var componentJs = glob.sync(`resources/assets/core/js/components/*.js`) || [];
var coreLayoutJs = glob.sync(`resources/assets/core/js/layout/*.js`) || [];

module.exports = [
    ...componentJs,
    ...coreLayoutJs,
];
