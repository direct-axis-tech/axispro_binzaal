const mix = require("laravel-mix");
const glob = require("glob");
const path = require("path");
const ReplaceInFileWebpackPlugin = require("replace-in-file-webpack-plugin");
const rimraf = require("rimraf");
const WebpackRTLPlugin = require("webpack-rtl-plugin");
const del = require("del");
const fs = require("fs");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// arguments/params from the line command
const args = getParameters();

// Global jquery
mix.autoload({
    jquery: ["$", "jQuery"],
    Popper: ["popper.js", "default"],
});

mix.options({
    cssNano: {
        discardComments: false,
    },
});

// Remove existing generated assets from public folder
del.sync(["public/css/*", "public/js/*", "public/plugins/*"]);

// Version everything that follows
mix.version();

// Build 3rd party plugins css/js
mix.sass(
    `resources/assets/core/plugins/plugins.scss`,
    `public/plugins/global/plugins.bundle.css`
)
    .then(() => {
        // remove unused preprocessed fonts folder
        rimraf(path.resolve("public/fonts"), () => {});
        rimraf(path.resolve("public/images"), () => {});
    })
    .sourceMaps(!mix.inProduction())
    // .setResourceRoot('./')
    .options({ processCssUrls: false })
    .scripts(
        require("./resources/assets/core/plugins/plugins.required.js"),
        `public/plugins/global/plugins-minimal.bundle.js`
    )
    .scripts(
        require("./resources/assets/core/plugins/plugins.js"),
        `public/plugins/global/plugins.bundle.js`
    )
    .scripts(
        require(`./resources/assets/core/js/scripts.js`),
        `public/js/core.bundle.js`
    );

// Build individual components
(glob.sync(`resources/assets/core/js/components/*.js`) || []).forEach(
    file => mix.scripts(
        file,
        `public/${file.replace('resources/assets/core/', '')}`
    )
);

// Build extended plugin styles
mix.sass(
    `resources/assets/sass/plugins.scss`,
    `public/plugins/global/plugins-custom.bundle.css`
);

// Build fa styles overrides
mix.sass(
    `resources/assets/sass/fa-overrides.scss`,
    `public/css/fa-overrides.css`
);

// Build Metronic css/js
mix.sass(
    `resources/assets/sass/style.scss`,
    `public/css/style.bundle.css`,
    { sassOptions: { includePaths: ["node_modules"] }, }
)   
    // .options({ processCssUrls: false })
    .sourceMaps(!mix.inProduction())
    .scripts(
        require('./resources/assets/js/src/utils.js'),
        'public/js/utils.bundle.js'
    )
    .scripts(
        require('./resources/assets/js/src/index.js'),
        'public/js/scripts.bundle.js'
    )
    .scripts(
        require('./resources/assets/js/fa-scripts.js'),
        'public/js/fa-scripts.bundle.js'
    );

// Dark skin mode css files
if (args.indexOf("dark_mode") !== -1) {
    mix.sass(
        `resources/assets/core/plugins/plugins.dark.scss`,
        `public/plugins/global/plugins.dark.bundle.css`
    );
    mix.sass(
        `resources/assets/sass/plugins.dark.scss`,
        `public/plugins/global/plugins-custom.dark.bundle.css`
    );
    mix.sass(
        `resources/assets/sass/style.dark.scss`,
        `public/css/style.dark.bundle.css`,
        { sassOptions: { includePaths: ["node_modules"] } }
    );
}

// Build custom 3rd party plugins
(glob.sync(`resources/assets/core/plugins/custom/**/*.js`) || []).forEach(
    (file) => {
        mix.js(
            file,
            `public/${file
                .replace(`resources/assets/core/`, "")
                .replace(".js", ".bundle.js")}`
        );
    }
);

(glob.sync(`resources/assets/core/plugins/custom/**/*.scss`) || []).forEach(
    (file) => {
        mix.sass(
            file,
            `public/${file
                .replace(`resources/assets/core/`, "")
                .replace(".scss", ".bundle.css")}`
        );
    }
);

// Build page specific scss files
(glob.sync(`resources/assets/sass/pages/**/!(_)*.scss`) || []).forEach(
    (file) => {
        file = file.replace(/[\\\/]+/g, "/");
        mix.sass(
            file,
            file
                .replace(`resources/assets/sass`, `public/css`)
                .replace(/\.scss$/, ".css")
        );
    }
);

let plugins = [
    new ReplaceInFileWebpackPlugin([
        {
            // rewrite font paths
            dir: path.resolve(`public/plugins/global`),
            test: /\.css$/,
            rules: [
                {
                    // fontawesome
                    search: /url\((\.\.\/)?webfonts\/(fa-.*?)"?\)/g,
                    replace: "url(./fonts/@fortawesome/$2)",
                },
                {
                    // flaticon
                    search: /url\(("?\.\/)?font\/(Flaticon\..*?)"?\)/g,
                    replace: "url(./fonts/flaticon/$2)",
                },
                {
                    // flaticon2
                    search: /url\(("?\.\/)?font\/(Flaticon2\..*?)"?\)/g,
                    replace: "url(./fonts/flaticon2/$2)",
                },
                {
                    // keenthemes fonts
                    search: /url\(("?\.\/)?(Ki\..*?)"?\)/g,
                    replace: "url(./fonts/keenthemes-icons/$2)",
                },
                {
                    // lineawesome fonts
                    search: /url\(("?\.\.\/)?fonts\/(la-.*?)"?\)/g,
                    replace: "url(./fonts/line-awesome/$2)",
                },
                {
                    // socicons
                    search: /url\(("?\.\.\/)?font\/(socicon\..*?)"?\)/g,
                    replace: "url(./fonts/socicon/$2)",
                },
                {
                    // bootstrap-icons
                    search: /url\(.*?(bootstrap-icons\..*?)"?\)/g,
                    replace: "url(./fonts/bootstrap-icons/$1)",
                },
                {
                    // fonticons
                    search: /url\(.*?(fonticons\..*?)"?\)/g,
                    replace: "url(./fonts/fonticons/$1)",
                },
            ],
        },
        {
            // rewrite configurations
            dir: path.resolve(`public/js`),
            test: /\.js$/,
            rules: [
                {
                    search: /process\.env\.(MIX_[A-Z0-0_]*)/g,
                    replace: function(match, p1) {
                        const v = parse(process.env[p1]);
                        return typeof v === 'string' ? `"${v}"` : v;
                    }
                }
            ]
        }
    ]),
];
if (args.indexOf("rtl") !== -1) {
    plugins.push(
        new WebpackRTLPlugin({
            filename: "[name].rtl.css",
            options: {},
            plugins: [],
            minify: false,
        })
    );
}

mix.webpackConfig({
    plugins: plugins,
    ignoreWarnings: [
        {
            module: /esri-leaflet/,
            message: /version/,
        },
    ],
});

// Webpack.mix does not copy fonts, manually copy
(
    glob.sync(`resources/assets/core/plugins/**/*.+(woff|woff2|eot|ttf|svg)`) ||
    []
).forEach((file) => {
    var folder = file.match(/resources\/.*?\/core\/plugins\/(.*?)\/.*?/)[1];
    mix.copy(
        file,
        `public/plugins/global/fonts/${folder}/${path.basename(file)}`
    );
});

(
    glob.sync(
        "node_modules/+(@fortawesome|socicon|line-awesome|bootstrap-icons)/**/*.+(woff|woff2|eot|ttf)"
    ) || []
).forEach((file) => {
    var folder = file.match(/node_modules\/(.*?)\//)[1];
    mix.copy(
        file,
        `public/plugins/global/fonts/${folder}/${path.basename(file)}`
    );
});

// Raw plugins
(glob.sync(`resources/assets/core/plugins/custom/**/*.js.json`) || []).forEach(
    (file) => {
        let filePaths = JSON.parse(fs.readFileSync(file, "utf-8"));
        const fileName = path.basename(file).replace(".js.json", "");
        mix.scripts(
            filePaths,
            `public/plugins/custom/${fileName}/${fileName}.bundle.js`
        );
    }
);

function getParameters() {
    var possibleArgs = ["dark_mode", "rtl"];
    for (var i = 0; i <= 13; i++) {
        possibleArgs.push("demo" + i);
    }

    var args = [];
    possibleArgs.forEach(function (key) {
        if (process.env["npm_config_" + key]) {
            args.push(key);
        }
    });

    return args;
}

/**
 * Parse the string if possible
 * 
 * @param {string} v 
 * @returns 
 */
function parse(v) {
    switch (v.toLowerCase()) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
        default:
            return v;
    }
}