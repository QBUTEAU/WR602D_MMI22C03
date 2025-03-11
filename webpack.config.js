const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Répertoire où les fichiers compilés seront stockés
    .setOutputPath('public/build/')
    // Chemin public utilisé par le serveur web pour accéder aux fichiers compilés
    .setPublicPath('/build')

    // Fichier d'entrée principal
    .addEntry('app', './assets/app.js')

    // Optimisation du code en fractionnant les fichiers
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    // Copie les images du dossier "assets/img" vers "public/build/images"
    .copyFiles({
        from: './assets/img', // Source des images
        to: 'images/[path][name].[ext]' // Destination dans public/build/images/
    })

    // Nettoie le dossier public/build avant chaque build
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Configuration de Babel
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

    // Active le support Sass/SCSS
    .enableSassLoader();

module.exports = Encore.getWebpackConfig();
