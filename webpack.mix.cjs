const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/css/app.scss', 'public/css') // usa postCss si es .css
   .sourceMaps()
   .version();
