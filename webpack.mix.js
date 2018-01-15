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

mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .version();

mix.styles([
    'resources/assets/css/welcome.css',
    'resources/assets/css/cabins.css'
], 'public/css/all.css');

mix.scripts([
    'resources/assets/js/welcome.js'
], 'public/js/all.js');
