<?php

namespace EvolutionCMS\EvoDirectoryEditor;

use EvolutionCMS\ServiceProvider;
use Illuminate\Support\Facades\Route;

class EvoDirectoryEditorServiceProvider extends ServiceProvider
{
    protected $namespace = 'DirectoryEditor';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadPluginsFrom(
            dirname(__DIR__) . '/plugins/'
        );
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        $this->loadViewsFrom(__DIR__ . '/../views', $this->namespace);

        app('router')->aliasMiddleware('directory-editor-csrf', \EvolutionCMS\EvoDirectoryEditor\Middlewares\CsrfToken::class);
        app('router')->aliasMiddleware('directory-editor-manager', \EvolutionCMS\EvoDirectoryEditor\Middlewares\AuthManager::class);


        $this->publishes([
            __DIR__ . '/../publishable/assets'  => MODX_BASE_PATH . 'assets',
        ]);

    }

    protected function mergeConfigFromCustom($path, $key)
    {
        if(is_dir($path)) {
            $files = glob($path . '/*.php');
            foreach($files as $file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $this->mergeConfigFrom($file, $key . '.' . $filename);
            }
        } else {
            if (is_file($path)) {
                $this->mergeConfigFrom($path, $key);
            }
        }
        return $this;
    }
}