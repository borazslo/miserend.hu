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
    .enableVersioning(true)
    .autoProvidejQuery()
    .addEntry('app', './assets/js/app.js')
    .addEntry('home', './assets/js/home.js')
;

module.exports = Encore.getWebpackConfig();
