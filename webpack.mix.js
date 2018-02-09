let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/* Laravel default css and js */
mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .version();


/* Js and css for each page */
mix.styles([
    'resources/assets/css/welcome.css',
    'resources/assets/css/search.css',
    'resources/assets/css/cabins.css',
    'resources/assets/css/frontend.css'
], 'public/css/all.css');

mix.scripts([
    'resources/assets/js/welcome.js',
    'resources/assets/js/search.js',
    'resources/assets/js/searchResult.js'
], 'public/js/all.js');


/* Js and css for plugins */
/*mix.styles([
    'resources/assets/css/plugins/select2.min.css'
], 'public/css/plugins.css'); */

mix.scripts([
    'resources/assets/js/plugins/typeahead.bundle.js'
], 'public/js/plugins.js');