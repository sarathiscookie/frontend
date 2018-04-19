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
    'resources/assets/css/frontend.css',
    'resources/assets/css/welcome.css',
    'resources/assets/css/search.css',
    'resources/assets/css/calendar.css',
    'resources/assets/css/searchResult.css',
    'resources/assets/css/cabinDetails.css',
    'resources/assets/css/cart.css',
    'resources/assets/css/inquiry.css',
    'resources/assets/css/payment.css',
    'resources/assets/css/login.css',
    'resources/assets/css/register.css'
], 'public/css/all.css');

mix.scripts([
    'resources/assets/js/welcome.js',
    'resources/assets/js/search.js',
    'resources/assets/js/calendar.js',
    'resources/assets/js/searchResult.js',
    'resources/assets/js/cabinDetails.js',
    'resources/assets/js/cart.js',
    'resources/assets/js/inquiry.js',
    'resources/assets/js/payment.js'
], 'public/js/all.js');

/* Js and css for plugins */
mix.styles([
    'resources/assets/css/plugins/lightslider.css'
], 'public/css/plugins.css');

mix.scripts([
    'resources/assets/js/plugins/typeahead.bundle.js',
    'resources/assets/js/plugins/lightslider.js'
], 'public/js/plugins.js');