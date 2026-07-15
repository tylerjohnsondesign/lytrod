let mix = require('laravel-mix');
mix.js('src/app.js', 'dist').setPublicPath('dist');
mix.sass('src/app.scss', 'dist');
mix.browserSync({
    proxy: 'http://localhost/lytrodrefactor/',
});


// let mix = require('laravel-mix');
// // mix.babel(['src/app.scss'], 'src/app.scss')
// mix.js('src/app.js', 'dist/app.js')
//     .babel(['dist/app.js'], 'dist/app.js')
// mix.sass('src/app.scss', 'dist/app.css');
// mix.browserSync({
//     proxy: 'http://localhost/lytrodrefactor/',
// });
