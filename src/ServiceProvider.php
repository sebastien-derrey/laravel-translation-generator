<?php

namespace Sdlab\TranslationGenerator;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateTranslationFiles::class,
            ]);
        }
    }
}
