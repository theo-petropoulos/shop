const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */

    /** Global */
    .addEntry('app', './assets/app.js')
    .addEntry('cart', './assets/js/cart.js')
    .addEntry('modal', './assets/js/modal.js')
    .addEntry('user_global_searchbar', './assets/js/searchbar.js')

    /** User */
    .addEntry('edit_delete_address', './assets/js/user/addresses/edit_delete_address.js')
    .addEntry('user_delete_account', './assets/js/user/delete_account.js')

    /** Admin */
    .addEntry('admin_products_searchbar', './assets/js/admin/products/searchbar.js')
    .addEntry('admin_dynamic_form', './assets/js/admin/products/dynamic_form.js')
    .addEntry('admin_dynamic_edit', './assets/js/admin/products/dynamic_edit.js')
    .addEntry('admin_delete_admin', './assets/js/admin/admins/delete_admin.js')

    /** Author */
    .addEntry('background_switcher', './assets/js/author/background_switcher.js')

    /** Product */
    .addEntry('product_zoom_hover', './assets/js/product/zoom_hover.js')
    .addEntry('suggestions', './assets/js/product/suggestions.js')
    .addEntry('loadmore', './assets/js/product/load_more.js')

    /** Checkout */
    .addEntry('checkout_select_address', './assets/js/checkout/user_select_address.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
