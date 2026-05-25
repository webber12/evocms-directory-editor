<?php

namespace EvolutionCMS\EvoDirectoryEditor;

use EvolutionCMS\ServiceProvider;
use Illuminate\Support\Facades\Route;

class EvoDirectoryEditorServiceProvider extends ServiceProvider
{
    protected $namespace = 'evodirectoryeditor';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
       
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        
       

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