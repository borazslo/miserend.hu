const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/static/')
    // public path used by the web server to access the output path
    .setPublicPath('/static')

    .copyFiles({
        from: './assets/img',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg)$/
    })

    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(true)
    .enableVersioning(!Encore.isDevServer())
    .autoProvidejQuery()
    .addEntry('app', './assets/js/app.js')
    .addEntry('home', './assets/js/home.js')
;

if (Encore.isDevServer()) {
    Encore.setPublicPath(`http://${process.env.MISEREND_HOSTNAME ?? 'connor.d'}:${process.env.MISEREND_WEBPACK_DEV_SERVER_PORT}/static`)
    Encore.setManifestKeyPrefix('static/')
    Encore.configureDevServerOptions(devServerOptions => {
        devServerOptions.allowedHosts = 'all'
        devServerOptions.host = '0.0.0.0'
        devServerOptions.port = process.env.MISEREND_WEBPACK_DEV_SERVER_PORT
        devServerOptions.hot = true
    })
}

module.exports = Encore.getWebpackConfig();
