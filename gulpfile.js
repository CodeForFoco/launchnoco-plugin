var elixir = require('laravel-elixir');

elixir.config.assetsPath = 'assets/';
elixir.config.publicPath = 'assets/';

elixir(function(mix) {
    mix.sass('admin/options.scss', 'assets/css/admin/options.css')
    mix.sass('admin/theme.scss', 'assets/css/admin/theme.css')
    mix.sass('theme.scss','assets/css/theme.css')
        .copy('node_modules/bootstrap-slider/dist/*.js', 'assets/js/vendor/bootstrap-slider/')
        .copy('node_modules/bootstrap-slider/dist/*.js', 'assets/js/vendor/bootstrap-slider/')
        .copy('node_modules/vue/dist/**/*.js', 'assets/js/vendor/vue/')
        .copy('node_modules/vue-filter/dist/*.js', 'assets/js/vendor/vue-filter/')
        .copy('node_modules/vue-slider-component/dist/*.js', 'assets/js/vendor/vue-slider-component/')
        .copy('node_modules/picturefill/dist/*.js', 'assets/js/vendor/picturefill/')
        .copy('node_modules/tether/dist/js/**/*.js', 'assets/js/vendor/tether/')
        .copy('node_modules/localforage/dist//*.js', 'assets/js/vendor/localforage/')
        .copy('node_modules/lodash/lodash.js', 'assets/js/vendor/lodash/')
        .copy('node_modules/lodash/lodash*.js', 'assets/js/vendor/lodash/')
        .copy('node_modules/bootstrap/dist/js/**/*.js', 'assets/js/vendor/bootstrap/')
        .copy('node_modules/bootstrap/dist/**/*.css', 'assets/css/vendor/bootstrap/')
        .copy('node_modules/jquery/dist/**/*.js', 'assets/js/vendor/jquery/')
        .copy('node_modules/select2/dist/js/*.js', 'assets/js/vendor/select2/')
        .copy('node_modules/vue-select/dist/*.js', 'assets/js/vendor/vue-select/')
        .copy('node_modules/select2/dist/js/i18n/*.js', 'assets/js/vendor/select2/i18n/')
        .copy('node_modules/select2/dist/css/*.css', 'assets/css/vendor/select2/')
        .copy('node_modules/slick-carousel/slick/*.js', 'assets/js/vendor/slick/')
        .copy('node_modules/slick-carousel/slick/*.css', 'assets/css/vendor/slick/')
        .copy('node_modules/slick-carousel/slick/*.gif', 'assets/css/vendor/slick/')
        .copy('node_modules/numeral/min/numeral.min.js', 'assets/js/inc/')
        .copy('node_modules/masonry-layout/dist/*.js', 'assets/js/vendor/masonry')
        .copy('node_modules/simplemde/dist/*.js', 'assets/js/vendor/simplemde')
        .copy('node_modules/simplemde/dist/*.css', 'assets/css/vendor/simplemde')
        .copy('node_modules/leaflet/dist/*.js', 'assets/js/vendor/leaflet')
        .copy('node_modules/leaflet/dist/*.css', 'assets/css/vendor/leaflet')
        .copy('node_modules/leaflet/dist/images', 'assets/css')
            .scripts(['assets/js/utils.js', 'assets/js/inc/', 'assets/js/main.js' ], 'assets/js/app.js')
            .scripts(['assets/js/utils.js', 'assets/js/admin/main.js'], "assets/js/admin/app.js");
});